{% extends "base.tpl" %}

{% block title %}Пополнение баланса{% endblock %}

{% block content %}
<h1>Пополнение баланса</h1>
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">ЮMoney</div>
            <div class="card-body">
                <form method="post" action="/payment/yoomoney">
                    <div class="mb-3">
                        <label class="form-label">Сумма (руб.)</label>
                        <input type="number" name="amount" class="form-control" min="1" max="50000" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Пополнить</button>
                </form>
            </div>
        </div>
    </div>
</div>
{% endblock %}