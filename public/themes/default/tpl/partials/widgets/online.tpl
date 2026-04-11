<div class="widget" id="online-widget">
    <div class="widget-header">
        <i class="fas fa-users me-2"></i> Сейчас на сайте (<span id="online-count">0</span>)
    </div>
    <div class="widget-body">
        <ul class="list-group list-group-flush" id="online-list">
            <li class="list-group-item text-muted">Загрузка...</li>
        </ul>
    </div>
</div>

<script>
function loadOnline() {
    fetch('/online/data')
        .then(response => response.json())
        .then(data => {
            document.getElementById('online-count').innerText = data.count;
            const list = document.getElementById('online-list');
            if (data.users.length === 0) {
                list.innerHTML = '<li class="list-group-item text-muted">Нет активных пользователей</li>';
                return;
            }
            let html = '';
            data.users.forEach(user => {
                html += `<li class="list-group-item d-flex align-items-center">
                            <img src="${user.avatar}" width="30" height="30" class="rounded-circle me-2">
                            <a href="/profile/${user.id}">${user.username}</a>
                            <span class="ms-auto small text-muted">${user.last_activity}</span>
                         </li>`;
            });
            list.innerHTML = html;
        })
        .catch(() => {
            document.getElementById('online-list').innerHTML = '<li class="list-group-item text-muted">Ошибка загрузки</li>';
        });
}
document.addEventListener('DOMContentLoaded', loadOnline);
setInterval(loadOnline, 30000);
</script>