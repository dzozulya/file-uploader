@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Uploaded Files</h1>

        <a href="{{ route('files.create') }}" class="btn btn-primary">
            Upload New File
        </a>
    </div>

    <div id="deleteSuccess" class="alert alert-success d-none"></div>
    <div id="deleteError" class="alert alert-danger d-none"></div>

    <div class="card">
        <div class="card-header">Files Management</div>
        <div class="card-body">
            @if($fileItems->isEmpty())
                <p class="mb-0">No files uploaded yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle mb-0">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Original Name</th>
                            <th>Type</th>
                            <th>Size</th>
                            <th>Uploaded At</th>
                            <th>Expires At</th>
                            <th style="width: 120px;">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($fileItems as $fileItem)
                            <tr id="file-row-{{ $fileItem->id }}">
                                <td>{{ $fileItem->id }}</td>
                                <td>{{ $fileItem->original_name }}</td>
                                <td>{{ strtoupper($fileItem->extension) }}</td>
                                <td>{{ number_format($fileItem->size / 1024, 2) }} KB</td>
                                <td>{{ $fileItem->uploaded_at?->format('Y-m-d H:i:s') }}</td>
                                <td>{{ $fileItem->expires_at?->format('Y-m-d H:i:s') }}</td>
                                <td>
                                    <button
                                        class="btn btn-danger btn-sm delete-btn"
                                        data-id="{{ $fileItem->id }}"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
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

        $('.delete-btn').on('click', function () {
            const fileId = $(this).data('id');

            $('#deleteSuccess').addClass('d-none').text('');
            $('#deleteError').addClass('d-none').text('');

            if (!confirm('Are you sure you want to delete this file?')) {
                return;
            }

            $.ajax({
                url: '/files/' + fileId,
                type: 'DELETE',
                success: function (response) {
                    $('#file-row-' + fileId).remove();

                    $('#deleteSuccess')
                        .removeClass('d-none')
                        .text(response.message);

                    if ($('tbody tr').length === 0) {
                        window.location.reload();
                    }
                },
                error: function () {
                    $('#deleteError')
                        .removeClass('d-none')
                        .text('Delete failed.');
                }
            });
        });
    </script>
@endpush
