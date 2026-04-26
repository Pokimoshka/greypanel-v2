<?php

declare(strict_types=1);

namespace GreyPanel\Controller;

use GreyPanel\Core\JsonResponse;
use GreyPanel\Core\Request;
use GreyPanel\Core\Response;
use GreyPanel\Core\View;
use GreyPanel\Interface\Service\SessionServiceInterface;
use GreyPanel\Interface\Service\ThemeServiceInterface;

final class AdminThemeEditorController
{
    private string $themesPath;
    private string $activeTheme;
    private array $allowedExtensions = ['tpl', 'html', 'twig', 'css', 'js', 'json', 'txt', 'md'];

    public function __construct(
        private ThemeServiceInterface $themeService,
        private SessionServiceInterface $session
    ) {
        $this->themesPath = __DIR__ . '/../../public/themes';
        $this->activeTheme = $this->themeService->getActiveTheme();
    }

    private function checkAccess(): void
    {
        if ($this->session->getUserGroup() < 4) {
            throw new \RuntimeException('Access denied', 403);
        }
    }

    private function getThemePath(): string
    {
        return $this->themesPath . '/' . $this->activeTheme;
    }

    // === Новый двухпанельный редактор ===
    public function editor(Request $request): Response
    {
        $this->checkAccess();
        $basePath = $this->getThemePath();
        $dir = $request->get('dir', '');
        $currentDir = $basePath . ($dir ? '/' . ltrim($dir, '/') : '');
        if (!is_dir($currentDir)) {
            $dir = '';
            $currentDir = $basePath;
        }
        $items = scandir($currentDir);
        $files = [];
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $full = $currentDir . '/' . $item;
            $isDir = is_dir($full);
            $ext = pathinfo($item, PATHINFO_EXTENSION);
            if (!$isDir && !in_array($ext, $this->allowedExtensions)) {
                continue;
            }
            $files[] = [
                'name' => $item,
                'path' => ($dir ? $dir . '/' : '') . $item,
                'is_dir' => $isDir,
                'size' => $isDir ? 0 : filesize($full),
                'modified' => date('Y-m-d H:i:s', filemtime($full)),
                'ext' => $ext,
            ];
        }
        usort($files, fn ($a, $b) => $a['is_dir'] === $b['is_dir'] ? strcmp($a['name'], $b['name']) : ($b['is_dir'] - $a['is_dir']));

        return new Response(View::render('theme_editor/editor.tpl', [
            'files' => $files,
            'current_dir' => $dir,
            'parent_dir' => dirname($dir) === '.' ? '' : dirname($dir),
            'active_theme' => $this->activeTheme,
        ]));
    }

    // === Получение содержимого файла (AJAX) ===
    public function getFileContent(Request $request): JsonResponse
    {
        $this->checkAccess();
        $file = $request->get('file');
        if (!$file) {
            return new JsonResponse(['error' => 'No file specified'], 400);
        }

        try {
            $fullPath = $this->resolvePath($file);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 403);
        }

        if (!is_file($fullPath)) {
            return new JsonResponse(['error' => 'File not found'], 404);
        }

        $ext = pathinfo($fullPath, PATHINFO_EXTENSION);
        if (!in_array($ext, $this->allowedExtensions)) {
            return new JsonResponse(['error' => 'Extension not allowed'], 403);
        }

        $content = file_get_contents($fullPath);
        return new JsonResponse(['content' => $content, 'ext' => $ext]);
    }

    // === Сохранение содержимого файла (AJAX) ===
    public function saveFileContent(Request $request): JsonResponse
    {
        $this->checkAccess();
        $file = $request->post('file');
        $content = $request->post('content');
        if (!$file) {
            return new JsonResponse(['error' => 'No file specified'], 400);
        }

        try {
            $fullPath = $this->resolvePath($file);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 403);
        }

        if (!is_file($fullPath)) {
            return new JsonResponse(['error' => 'File not found'], 404);
        }

        // Бэкап
        $backupDir = $this->getThemePath() . '/.backups';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        copy($fullPath, $backupDir . '/' . basename($file) . '.' . time() . '.bak');

        file_put_contents($fullPath, $content);
        return new JsonResponse(['success' => true]);
    }

    // === Остальные методы (создание, удаление) – как были ===
    public function create(Request $request): JsonResponse
    {
        $this->checkAccess();
        $dir = $request->post('dir', '');
        $name = trim($request->post('name'));
        $type = $request->post('type');
        if (!$name) {
            return new JsonResponse(['error' => 'Name required'], 400);
        }

        // Проверяем, что родительская директория внутри темы
        try {
            $parentPath = $dir ? $this->resolvePath($dir) : $this->getThemePath();
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 403);
        }

        $fullPath = $parentPath . '/' . $name;
        if (file_exists($fullPath)) {
            return new JsonResponse(['error' => 'Already exists'], 400);
        }

        if ($type === 'folder') {
            // Разрешаем только имена папок без расширений: буквы, цифры, подчёркивание
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $name)) {
                return new JsonResponse(['error' => 'Folder name can only contain letters, digits, underscores'], 400);
            }
            mkdir($fullPath, 0755, true);
        } else {
            $ext = pathinfo($fullPath, PATHINFO_EXTENSION);
            if (!in_array($ext, $this->allowedExtensions)) {
                return new JsonResponse(['error' => 'Extension not allowed'], 403);
            }
            file_put_contents($fullPath, '');
        }
        return new JsonResponse(['success' => true]);
    }

    public function delete(Request $request): JsonResponse
    {
        $this->checkAccess();
        $file = $request->post('file');
        if (!$file) {
            return new JsonResponse(['error' => 'File not specified'], 400);
        }

        try {
            $fullPath = $this->resolvePath($file);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 403);
        }

        if (!file_exists($fullPath)) {
            return new JsonResponse(['error' => 'Not found'], 404);
        }

        if (is_dir($fullPath)) {
            $this->deleteDir($fullPath);
        } else {
            unlink($fullPath);
        }
        return new JsonResponse(['success' => true]);
    }

    private function deleteDir(string $dir): void
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDir($path) : unlink($path);
        }
        rmdir($dir);
    }

    private function resolvePath(string $relativePath): string
    {
        $base = realpath($this->getThemePath());
        if ($base === false) {
            throw new \RuntimeException('Theme directory does not exist');
        }

        $requestedPath = $base . '/' . ltrim($relativePath, '/');
        if (is_link($requestedPath)) {
            throw new \RuntimeException('Symlinks are not allowed');
        }

        $fullPath = realpath($requestedPath);

        if ($fullPath === false || strpos($fullPath, $base) !== 0) {
            throw new \RuntimeException('Access denied: path traversal detected');
        }

        return $fullPath;
    }
}
