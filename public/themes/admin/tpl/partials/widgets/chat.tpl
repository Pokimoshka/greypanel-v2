<div x-data="chatWidget" x-init="init" class="widget">
    <div class="widget-header">
        <i class="fas fa-comment me-2"></i> Чат
    </div>
    <div class="widget-body" style="max-height: 400px; overflow-y: auto;" x-ref="messagesContainer">
        <ul class="list-group list-group-flush">
            <template x-for="msg in messages" :key="msg.id">
                <li class="list-group-item">
                    <div class="d-flex">
                        <img :src="msg.avatar" width="30" height="30" class="rounded-circle me-2">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <strong x-text="msg.username"></strong>
                                <small class="text-muted" x-text="msg.time"></small>
                            </div>
                            <div x-html="msg.text"></div>
                        </div>
                    </div>
                </li>
            </template>
        </ul>
    </div>
    {% if app.user %}
    <div class="widget-footer p-2">
        <form @submit.prevent="sendMessage">
            <div class="input-group">
                <input type="text" class="form-control" x-model="newMessage" placeholder="Сообщение...">
                <button class="btn btn-primary" type="submit">Отправить</button>
            </div>
        </form>
    </div>
    {% endif %}
</div>