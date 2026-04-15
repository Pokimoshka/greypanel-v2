<footer class="app-footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 mb-3">
                <h5>{{ site_name }}</h5>
                <p class="text-secondary">Современная панель управления игровыми серверами.</p>
            </div>
            <div class="col-md-4 mb-3">
                <h5>Навигация</h5>
                <ul class="list-unstyled">
                    <li><a href="{{ url('/') }}" class="text-secondary text-decoration-none">Главная</a></li>
                    <li><a href="{{ url('/forum') }}" class="text-secondary text-decoration-none">Форум</a></li>
                    <li><a href="{{ url('/news') }}" class="text-secondary text-decoration-none">Новости</a></li>
                    <li><a href="{{ url('/monitor') }}" class="text-secondary text-decoration-none">Мониторинг</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-3">
                <h5>Контакты</h5>
                <ul class="list-unstyled">
                    <li><i class="fab fa-vk me-2" style="color: var(--accent);"></i> <a href="#" class="text-secondary text-decoration-none">ВКонтакте</a></li>
                    <li><i class="fab fa-telegram me-2" style="color: var(--accent);"></i> <a href="#" class="text-secondary text-decoration-none">Telegram</a></li>
                    <li><i class="fab fa-discord me-2" style="color: var(--accent);"></i> <a href="#" class="text-secondary text-decoration-none">Discord</a></li>
                </ul>
            </div>
        </div>
        <hr style="border-color: var(--border-color);">
        <div class="text-center text-secondary">
            &copy; {{ "now"|date("Y") }} {{ site_name }}. Все права защищены.
        </div>
    </div>
</footer>