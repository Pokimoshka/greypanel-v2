{% for item in files %}
    <li>
        {% if item.is_dir %}
            <a class="folder" data-path="{{ item.path }}" onclick="ThemeEditor.loadDir('{{ item.path }}')">
                <i class="fas fa-folder"></i> {{ item.name }}
            </a>
            <ul id="dir-{{ item.path|replace({'/': '_'}) }}" style="display: none;"></ul>
        {% else %}
            <a class="file" data-path="{{ item.path }}" onclick="ThemeEditor.loadFile('{{ item.path }}')">
                <i class="fas fa-file-code"></i> {{ item.name }}
            </a>
        {% endif %}
    </li>
{% endfor %}