<div class="widget">
    <div class="widget-header">
        <i class="fas fa-comments me-2"></i> Последние темы
    </div>
    <div class="widget-body">
        <ul class="list-group list-group-flush">
            {% for topic in last_topics %}
            <li class="list-group-item">
                <a href="/forum/thread/{{ topic.id }}" class="fw-bold">{{ topic.title|slice(0, 55) }}</a>
                <div class="small text-muted mt-1">
                    <i class="fas fa-user me-1"></i> {{ topic.author_name }} 
                    <i class="fas fa-clock ms-2 me-1"></i> {{ topic.created_at|date('d.m.Y') }}
                    <i class="fas fa-reply ms-2 me-1"></i> {{ topic.replies }}
                </div>
            </li>
            {% else %}
            <li class="list-group-item text-muted">Нет тем</li>
            {% endfor %}
        </ul>
    </div>
    <div class="widget-footer text-center p-2">
        <a href="/forum" class="small text-primary">Все темы →</a>
    </div>
</div>