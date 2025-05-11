require('./bootstrap');

document.addEventListener('DOMContentLoaded', () => {

const monthForm = document.querySelector('.select-month-form');
    const monthInput = monthForm?.querySelector('input[name="selected_month"]');

    if (monthForm && monthInput) {
        monthInput.addEventListener('change', () => {
            monthForm.submit();
        });
    }


    // ▼ 日付（input type="date"）が変更されたら自動でフォーム送信
    const datePicker = document.getElementById('admin-date-picker');
    if (datePicker) {
        datePicker.addEventListener('change', () => {
            const form = document.getElementById('admin-date-form');
            if (form) {
                form.submit();
            }
        });
    }

    // ▼ 月（input type="month"）が変更されたら自動でフォーム送信
    const monthPicker = document.getElementById('admin-month-picker');
    if (monthPicker) {
        monthPicker.addEventListener('change', () => {
            const form = document.getElementById('admin-month-form');
            if (form) {
                form.submit();
            }
        });
    }
});
