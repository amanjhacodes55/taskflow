/**
 * The project page: add, update and delete tasks with AJAX, so the page never reloads.
 * The server answers with the task row already rendered (the same Blade component the page
 * uses), plus the new counts for the filter buttons.
 */
$(function () {
    // Laravel rejects POST/PATCH/DELETE without the CSRF token (419), so send it on every request.
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), Accept: 'application/json' } });

    const $list = $('#task-list');
    const $form = $('#task-form');
    let currentFilter = 'all';

    function toast(message) {
        $('#toast .toast-body').text(message);              // .text(), never .html(): no XSS
        bootstrap.Toast.getOrCreateInstance(document.getElementById('toast')).show();
    }

    function updateCounts(counts) {
        $.each(counts, (status, count) => $(`[data-count="${status}"]`).text(count));
        $('#empty-state').toggleClass('d-none', counts.all > 0);
    }

    function applyFilter() {
        $list.children('.task-row').each(function () {
            const visible = currentFilter === 'all' || $(this).data('status') === currentFilter;
            $(this).toggleClass('d-none', !visible);
        });
    }

    function showErrors(xhr) {
        if (xhr.status === 422) {                           // Laravel validation errors
            $.each(xhr.responseJSON.errors, (field, messages) => {
                $form.find(`[name="${field}"]`).addClass('is-invalid');
                $form.find(`[data-error-for="${field}"]`).text(messages[0]);
            });
        } else {
            toast(xhr.status === 403 ? 'You can’t change this task.' : 'Something went wrong. Please try again.');
        }
    }

    function updateTask($row, data) {
        $.ajax({ url: `/tasks/${$row.data('id')}`, method: 'PATCH', data })
            .done(res => {
                const $new = $(res.html);
                $row.replaceWith($new);
                updateCounts(res.counts);
                applyFilter();
            })
            .fail(showErrors);
    }

    // Add a task
    $form.on('submit', function (e) {
        e.preventDefault();
        $form.find('.is-invalid').removeClass('is-invalid');
        const $button = $form.find('button[type="submit"]').prop('disabled', true);

        $.post($form.data('url'), $form.serialize())
            .done(res => {
                $list.prepend(res.html);
                updateCounts(res.counts);
                applyFilter();
                $form.find('[name="title"], [name="due_at"]').val('');
                toast('Task added');
            })
            .fail(showErrors)
            .always(() => $button.prop('disabled', false));
    });

    // Event delegation: these handlers also work for rows added later by AJAX.
    $list.on('change', '.js-toggle', function () {
        updateTask($(this).closest('.task-row'), { status: this.checked ? 'done' : 'todo' });
    });

    $list.on('change', '.js-status', function () {
        updateTask($(this).closest('.task-row'), { status: $(this).val() });
    });

    $list.on('click', '.js-delete', function () {
        const $row = $(this).closest('.task-row');
        if (!confirm('Delete this task?')) return;

        $.ajax({ url: `/tasks/${$row.data('id')}`, method: 'DELETE' })
            .done(res => {
                $row.fadeOut(200, () => { $row.remove(); updateCounts(res.counts); });
                toast('Task deleted');
            })
            .fail(showErrors);
    });

    $('#filters').on('click', 'button', function () {
        currentFilter = $(this).data('filter');
        $(this).addClass('active').siblings().removeClass('active');
        applyFilter();
    });
});
