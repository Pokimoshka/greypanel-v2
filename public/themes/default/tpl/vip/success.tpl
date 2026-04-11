{% extends "base.tpl" %}

{% block title %}Привилегия активирована{% endblock %}

{% block content %}
<div class="alert alert-success">
    <h4>Поздравляем!</h4>
    <p>Привилегия успешно активирована. Теперь вы можете заходить на сервер с новыми возможностями.</p>
    <p>Не забудьте установить пароль в консоли: <code>setinfo "_pw" "{{ app.user.username }}"</code></p>
    <a href="/vip" class="btn btn-primary">Вернуться к списку серверов</a>
</div>
{% endblock %}