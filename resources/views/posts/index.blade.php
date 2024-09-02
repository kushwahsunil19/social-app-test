@extends('auth.layouts')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<div class="container mt-5">
    <h1>Posts</h1>

    <!-- Button to trigger modal -->
    <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addPostModal">Add Post</button>

    <!-- Table to display posts -->
    <table id="postTable" class="display table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Description</th>
                <th>User Name</th>
                <th>Created At</th>
                <th>Likes</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>

    <!-- Modal for adding a post -->
    <div class="modal fade" id="addPostModal" tabindex="-1" role="dialog" aria-labelledby="addPostModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPostModalLabel">Add Post</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addPostForm">
                        @csrf
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Post</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        
        // Toastr configuration
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": false,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };
        var userId = {{ Auth::id() }};
        // Initialize DataTable
        var table = $('#postTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("posts.index") }}',
        columns: [
            { data: 'id', name: 'id' },
            { data: 'title', name: 'title' },
            { data: 'description', name: 'description' },
            { data: 'user.name', name: 'user.name' },
            { data: 'created_at', name: 'created_at' },
            { data: 'likes_count', name: 'likes_count' }, // Column for likes count
            { 
                data: 'user_id', // Assuming 'user_id' is included in your data response
                name: 'user_id',
                render: function (data, type, row) {
                    if (data === userId) {
                        return ''; // Hide actions if it's the current user's post
                    } else {
                        return `
                            <button class="like-button btn btn-success" data-post-id="${row.id}">Like</button>
                            <button class="unlike-button btn btn-danger" data-post-id="${row.id}">Unlike</button>
                        `;
                    }
                },
                orderable: false,
                searchable: false
            }
        ]
    });
        // Handle form submission for adding new posts
        $('#addPostForm').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                type: 'POST',
                url: '{{ route("posts.store") }}',
                data: $(this).serialize(),
                success: function(response) {
                    toastr.success(response.message);
                    table.ajax.reload(); // Reload DataTable
                    $('#addPostModal .close').click();
                    $('#addPostForm')[0].reset(); // Reset the form
                },
                error: function(response) {
                    toastr.error(response.responseJSON.message);
                }
            });
        });
        // Debug: Test if the modal hides properly
     
        // Handle like button click
        $(document).on('click', '.like-button', function() {
            var postId = $(this).data('post-id');

            $.ajax({
                type: 'POST',
                url: '/posts/' + postId + '/like',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    toastr.success(response.message);
                    table.ajax.reload(); // Reload DataTable
                },
                error: function(response) {
                    toastr.error(response.responseJSON.message);
                }
            });
        });

        // Handle unlike button click
        $(document).on('click', '.unlike-button', function() {
            var postId = $(this).data('post-id');

            $.ajax({
                type: 'POST',
                url: '/posts/' + postId + '/unlike',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    toastr.success(response.message);
                    table.ajax.reload(); // Reload DataTable
                },
                error: function(response) {
                    toastr.error(response.responseJSON.message);
                }
            });
        });
    });
</script>

@endsection
