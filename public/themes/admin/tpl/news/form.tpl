{% extends "base.tpl" %}

{% block title %}{{ news.id ? 'Редактирование' : 'Добавление' }} новости{% endblock %}

{% block content %}
<h1>{{ news.id ? 'Редактирование' : 'Добавление' }} новости</h1>

<form method="post">
    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
    <div class="mb-3">
        <label class="form-label">Заголовок</label>
        <input type="text" name="title" class="form-control" value="{{ news.title|default('') }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Slug (URL)</label>
        <input type="text" name="slug" class="form-control" value="{{ news.slug|default('') }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Содержание</label>
        <textarea name="content" id="editor" class="form-control editor" rows="15">{{ news.content|default('') }}</textarea>
    </div>
    <div class="mb-3 form-check">
        <input type="checkbox" name="is_published" class="form-check-input" id="is_published" value="1" {{ news.is_published|default(true) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_published">Опубликовать</label>
    </div>
    <button type="submit" class="btn btn-primary">Сохранить</button>
    <a href="{{ url('admin/news') }}" class="btn btn-secondary">Отмена</a>
</form>
{% endblock %}

{% block scripts %}
{{ parent() }}
<script>
    const easyMDE = new EasyMDE({
        element: document.getElementById('editor'),
        spellChecker: true,
        uploadImage: true,
        imageUploadEndpoint: '/admin/upload-image',
        toolbar: [
            'bold', 'italic', 'heading', '|',
            'quote', 'unordered-list', 'ordered-list', 'table', 'horizontal-rule', '|',
            'link', 'image', 'upload-image', '|',
            'preview', 'side-by-side', 'fullscreen', '|',
            'guide'
        ],
        previewRender: (plainText) => {
            return easyMDE.markdown(plainText);
        }
    });
</script>
{% endblock %}