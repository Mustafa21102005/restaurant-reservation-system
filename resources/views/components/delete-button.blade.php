{{-- Delete Button --}}
<button type="button" class="btn btn-outline-danger me-1" data-bs-toggle="modal"
    data-bs-target="#deleteModal{{ $resourceId }}">
    Delete
</button>

{{-- Delete  Modal --}}
<div class="modal fade" id="deleteModal{{ $resourceId }}" tabindex="-1" aria-labelledby="deleteModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Delete Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ $resourceRoute }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
