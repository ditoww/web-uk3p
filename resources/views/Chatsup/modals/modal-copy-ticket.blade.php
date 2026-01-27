<!-- Modal -->
<div class="modal fade" id="modalCopyTicket" tabindex="-1" aria-labelledby="modalCopyTicketLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modalCopyTicketLabel">Copy Ticket</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body position-relative">

                <div id="modalCopyTicketLoading" class="d-flex flex-column justify-content-center align-items-center"
                    style="min-height:300px;">
                    <div class="spinner-border text-primary mb-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <small class="text-muted">Memuat data tiket...</small>
                </div>

                <form method="POST" action="{{ route('SupportClientChat.copyDevTicket') }}"
                    enctype="multipart/form-data" id="create-dev-ticket-form" class="d-none">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Judul Tiket <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="title" class="form-control"
                            placeholder="Judul Ticket" value="{{ old('title') }}"
                            oninput="validateTitleInput(this);" />
                        <input type="hidden" value="" name="ticket_id" id="ticket_id">
                    </div>

                    <div class="mb-2 position-relative">
                        <label class="form-label">Deskripsi <span class="text-danger">*</span></label>
                        <textarea name="description" id="description" class="form-control" rows="8" maxlength="10000"
                            placeholder="Deskripsi issue untuk developer...">{{ old('description') }}</textarea>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <small id="description-word-count" class="text-muted">0 / 10000</small>
                        <small id="description-error" class="text-danger" style="display:none;">Deskripsi tersebut
                            terlalu panjang</small>
                    </div>

                    <div class="mb-2 position-relative">
                        <label class="form-label">Attachment Sebelumnya</label>
                        <div class="attachment-old">
                            <img id="oldAttachmentPreview" src="" alt="Attachment"
                                class="img-fluid rounded d-none" style="max-height:200px;">
                            <small id="noAttachmentText" class="text-muted d-none">
                                Tidak ada attachment
                            </small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Attachment (opsional)</label>
                        <div class="d-flex align-items-center">
                            <input type="file" id="devTicketImageInput" name="image"
                                accept=".jpg,.jpeg,.png,.webp,.xlsx,.docx,.pdf" class="form-control w-75" />
                            <div class="mx-4">
                                <button type="button" class="btn btn-sm btn-outline-danger" id="removeAttachmentBtn">
                                    <i class="bi bi-x-circle me-1"></i> Hapus Attachment
                                </button>
                            </div>
                        </div>
                        <small class="text-muted">Max 5MB. JPG / PNG / WEBP / PDF / DOCX / XLSX (gambar akan dikompres
                            otomatis).</small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" id="btn-submit">
                            <i class="bi bi-check2-circle me-1"></i> Buat
                        </button>
                        <a href="{{ request('ticket') ? route('SupportClientChat.supportclientchat', ['ticket' => request('ticket')]) : route('SupportClientChat.supportclientchat') }}"
                            class="btn btn-light">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $('#modalCopyTicket').on('shown.bs.modal', function(e) {
        const button = $(e.relatedTarget);
        const ticket_id = button.data('id');

        $('#modalCopyTicketLoading').removeClass('d-none');
        $('#create-dev-ticket-form').addClass('d-none');

        $('#ticket_id').val(ticket_id);
        $.ajax({
                url: "{{ route('api.ticket.single', ':id') }}".replace(':id', ticket_id),
                method: 'GET',
            })
            .done(function(res) {
                $('#title').val(res.data.title);
                $('#description').val(res.data.chat.response);

                const attachment = res.data.chat?.attachment;

                if (attachment && attachment.filePath) {
                    let imageUrl = attachment.filePath;

                    // kalau backend kirim relative path
                    if (!imageUrl.startsWith('http')) {
                        imageUrl = "{{ asset('') }}" + imageUrl;
                    }

                    $('#oldAttachmentPreview')
                        .attr('src', imageUrl)
                        .removeClass('d-none');

                    $('#noAttachmentText').addClass('d-none');
                } else {
                    $('#oldAttachmentPreview').addClass('d-none');
                    $('#noAttachmentText').removeClass('d-none');
                }
            })
            .fail(function() {
                alert('Gagal memuat data tiket');
                $('#modalCopyTicket').modal('hide');
            })
            .always(function() {
                $('#modalCopyTicketLoading').addClass('d-none');
                $('#create-dev-ticket-form').removeClass('d-none');
            });
    });
</script>

<script>
    const fileInput = document.getElementById('devTicketImageInput');
    const fields = ['title', 'description'];


    function getCaseType() {
        var checked = document.querySelector('input[name="case_type"]:checked');
        return checked ? checked.value : 'ticket';
    }

    function validateAndSubmit(e) {
        e.preventDefault();
        const form = document.getElementById('create-dev-ticket-form');
        const btn = document.getElementById('btn-submit');
        clearAjaxErrors();

        const fd = new FormData(form);
        fd.append('_token', '{{ csrf_token() }}');

        btn.disabled = true;
        btn.innerHTML =
            '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Buat';

        $.ajax({
            url: '{{ route('SupportClientChat.validateDevTicket') }}',
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function() {
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    HTMLFormElement.prototype.submit.call(form);
                }
            },
            error: function(xhr) {
                btn.disabled = false;
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    showAjaxErrors(xhr.responseJSON.errors);
                } else {
                    console.error('Validasi AJAX gagal:', xhr);
                }
            },
            complete: function() {
                btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Buat';
            }
        });
    }

    function clearAjaxErrors() {
        fileInput.readOnly = true;
        const byp = document.getElementById('byp');
        const err = document.querySelector('#err');
        if (byp) byp.textContent = '';
        if (err) {
            err.innerHTML = "";
            err.classList.toggle('d-none', true)
        }
        if (getCaseType() === 'ticket') {
            window.currentAnchor = null;
        } else {
            const role = document.getElementById('role_id');
            role.readOnly = true;
        }
        fields.forEach(function(name) {
            const input = document.getElementById(name);
            input.readOnly = true;
        })
    }
</script>
