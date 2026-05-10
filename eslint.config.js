import js from '@eslint/js';

export default [
    js.configs.recommended,
    {
        files: ['resources/js/**/*.js'],
        languageOptions: {
            ecmaVersion: 2022,
            sourceType: 'module',
            globals: {
                window: 'readonly',
                document: 'readonly',
                fetch: 'readonly',
                localStorage: 'readonly',
                Toast: 'readonly',
                Alpine: 'readonly',
                bootstrap: 'readonly',
                hljs: 'readonly',
                Sortable: 'readonly',
                marked: 'readonly',
                EasyMDE: 'readonly',
                Picker: 'readonly',
            },
        },
        rules: {
            'no-unused-vars': ['warn', { argsIgnorePattern: '^_' }],
            'no-console': 'off',
            'prefer-const': 'error',
            'no-var': 'error',
            'eqeqeq': ['error', 'always'],
        },
    },
];