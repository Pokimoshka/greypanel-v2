{% if total > per_page %}
    {% set last_page = (total / per_page)|round(0, 'ceil') %}
    <nav>
        <ul class="pagination">
            {% if current > 1 %}
                <li class="page-item"><a class="page-link" href="{{ url }}?page={{ current - 1 }}{% for key, value in params %}&{{ key }}={{ value }}{% endfor %}">«</a></li>
            {% endif %}
            {% for p in 1..last_page %}
                <li class="page-item {{ p == current ? 'active' : '' }}">
                    <a class="page-link" href="{{ url }}?page={{ p }}{% for key, value in params %}&{{ key }}={{ value }}{% endfor %}">{{ p }}</a>
                </li>
            {% endfor %}
            {% if current < last_page %}
                <li class="page-item"><a class="page-link" href="{{ url }}?page={{ current + 1 }}{% for key, value in params %}&{{ key }}={{ value }}{% endfor %}">»</a></li>
            {% endif %}
        </ul>
    </nav>
{% endif %}