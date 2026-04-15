{% if total > per_page %}
    {% set last_page = (total / per_page)|round(0, 'ceil') %}
    <div class="pagination-wrapper">
        <ul class="pagination">
            {% if current > 1 %}
                <li class="page-item">
                    <a class="page-link" href="{{ url }}?page={{ current - 1 }}{% for key, value in params %}&{{ key }}={{ value }}{% endfor %}">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
            {% else %}
                <li class="page-item disabled">
                    <span class="page-link"><i class="fas fa-chevron-left"></i></span>
                </li>
            {% endif %}

            {% set start = max(1, current - 2) %}
            {% set end = min(last_page, current + 2) %}

            {% if start > 1 %}
                <li class="page-item">
                    <a class="page-link" href="{{ url }}?page=1{% for key, value in params %}&{{ key }}={{ value }}{% endfor %}">1</a>
                </li>
                {% if start > 2 %}
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                {% endif %}
            {% endif %}

            {% for p in start..end %}
                <li class="page-item {{ p == current ? 'active' : '' }}">
                    <a class="page-link" href="{{ url }}?page={{ p }}{% for key, value in params %}&{{ key }}={{ value }}{% endfor %}">{{ p }}</a>
                </li>
            {% endfor %}

            {% if end < last_page %}
                {% if end < last_page - 1 %}
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                {% endif %}
                <li class="page-item">
                    <a class="page-link" href="{{ url }}?page={{ last_page }}{% for key, value in params %}&{{ key }}={{ value }}{% endfor %}">{{ last_page }}</a>
                </li>
            {% endif %}

            {% if current < last_page %}
                <li class="page-item">
                    <a class="page-link" href="{{ url }}?page={{ current + 1 }}{% for key, value in params %}&{{ key }}={{ value }}{% endfor %}">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            {% else %}
                <li class="page-item disabled">
                    <span class="page-link"><i class="fas fa-chevron-right"></i></span>
                </li>
            {% endif %}
        </ul>
    </div>
{% endif %}