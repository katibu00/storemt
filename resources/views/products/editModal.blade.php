<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="edit-product-form">
                    <input type="hidden" id="edit-product-id" name="product_id">
                    <div class="mb-3">
                        <label for="edit-product-name" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="edit-product-name" name="product_name">
                    </div>
                    <div class="mb-3">
                        <label for="edit-buying-price" class="form-label">Buying Price</label>
                        <input type="number" step="any" class="form-control" id="edit-buying-price"
                            name="buying_price">
                    </div>
                    <div class="mb-3">
                        <label for="edit-selling-price" class="form-label">Selling Price</label>
                        <input type="number" step="any" class="form-control" id="edit-selling-price"
                            name="selling_price">
                    </div>
                    <div class="mb-3">
                        <label for="edit-quantity" class="form-label">Quantity</label>
                        <input type="number" step="any" class="form-control" id="edit-quantity" name="quantity">
                    </div>
                    <div class="mb-3">
                        <label for="edit-alert-level" class="form-label">Alert Level</label>
                        <input type="number" step="any" class="form-control" id="edit-alert-level"
                            name="alert_level">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
