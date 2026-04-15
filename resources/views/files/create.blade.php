@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="mb-4">Upload File</h1>

            <div class="card">
                <div class="card-header">Upload PDF / DOCX</div>
                <div class="card-body">
                    <form id="uploadForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="file" class="form-label">Select file</label>
                            <input
                                type="file"
                                name="file"
                                id="file"
                                class="form-control"
                                accept=".pdf,.docx"
                                required
                            >
                            <div class="form-text">
                                Allowed formats: PDF, DOCX. Maximum size: 10 MB.
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" id="uploadButton">
                                Upload
                            </button>

                            <a href="{{ route('files.index') }}" class="btn btn-outline-secondary">
                                Go to Files List
                            </a>
                        </div>
                    </form>

                    <div id="uploadSuccess" class="alert alert-success mt-3 d-none"></div>
                    <div id="uploadError" class="alert alert-danger mt-3 d-none"></div>

                    <div class="progress mt-3 d-none" id="uploadProgressWrapper">
                        <div
                            id="uploadProgressBar"
                            class="progress-bar"
                            role="progressbar"
                            style="width: 0%;"
                        >
                            0%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#uploadForm').on('submit', function (e) {
            e.preventDefault();

            $('#uploadSuccess').addClass('d-none').text('');
            $('#uploadError').addClass('d-none').text('');
            $('#uploadProgressWrapper').removeClass('d-none');
            $('#uploadProgressBar').css('width', '0%').text('0%');
            $('#uploadButton').prop('disabled', true).text('Uploading...');

            const formData = new FormData(this);

            $.ajax({
                url: '{{ route('files.store') }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function () {
                    const xhr = new window.XMLHttpRequest();

                    xhr.upload.addEventListener('progress', function (event) {
                        if (event.lengthComputable) {
                            const percent = Math.round((event.loaded / event.total) * 100);
                            $('#uploadProgressBar').css('width', percent + '%').text(percent + '%');
                        }
                    });

                    return xhr;
                },
                success: function (response) {
                    $('#uploadSuccess')
                        .removeClass('d-none')
                        .text(response.message + ' Redirecting to files list...');

                    $('#uploadForm')[0].reset();

                    setTimeout(function () {
                        window.location.href = '{{ route('files.index') }}';
                    }, 800);
                },
                error: function (xhr) {
                    let message = 'Upload failed.';

                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.errors) {
                            message = Object.values(xhr.responseJSON.errors).flat().join(' ');
                        } else if (xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                    }

                    $('#uploadError').removeClass('d-none').text(message);
                },
                complete: function () {
                    $('#uploadButton').prop('disabled', false).text('Upload');

                    setTimeout(function () {
                        $('#uploadProgressWrapper').addClass('d-none');
                    }, 500);
                }
            });
        });
    </script>
@endpush
