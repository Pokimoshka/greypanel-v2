<footer class="app-footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 mb-3">
                <h5>{{ site_name }}</h5>
                <p class="text-secondary">{{ trans('site.description') }}</p>
            </div>
            <div class="col-md-4 mb-3">
                <h5>{{ trans('footer.navigation') }}</h5>
                <ul class="list-unstyled">
                    <li><a href="{{ url('/') }}" class="text-secondary text-decoration-none">{{ trans('nav.home') }}</a></li>
                    <li><a href="{{ url('/forum') }}" class="text-secondary text-decoration-none">{{ trans('nav.forum') }}</a></li>
                    <li><a href="{{ url('/news') }}" class="text-secondary text-decoration-none">{{ trans('nav.news') }}</a></li>
                    <li><a href="{{ url('/monitor') }}" class="text-secondary text-decoration-none">{{ trans('nav.monitor') }}</a></li>
                    <li><a href="{{ url('/stats') }}" class="text-secondary text-decoration-none">{{ trans('nav.stats') }}</a></li>
                    <li><a href="{{ url('/bans') }}" class="text-secondary text-decoration-none">{{ trans('nav.bans') }}</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-3">
                <h5>{{ trans('footer.contacts') }}</h5>
                <ul class="list-unstyled">
                    <li><i class="fab fa-discord me-2" style="color: var(--accent);"></i> <a href="#" class="text-secondary text-decoration-none">Discord</a></li>
                    <li><i class="fab fa-telegram me-2" style="color: var(--accent);"></i> <a href="#" class="text-secondary text-decoration-none">Telegram</a></li>
                </ul>
            </div>
        </div>
        <hr style="border-color: var(--border-color);">
        <div class="text-center text-secondary">
            {{ trans('footer.copyright', {'%year%': "now"|date("Y"), '%site_name%': site_name}) }}
        </div>
    </div>
</footer>