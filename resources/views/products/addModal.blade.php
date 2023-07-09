    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="add-products-form">
                    <div class="modal-body">
                        @if (auth()->user()->business->has_branches == 1)
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="productName" class="form-label">Branch</label>
                                        <select class="form-select" name="branch_id">
                                            <option value="">--select branch--</option>
                                            @foreach ($branches as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <!-- Add more rows dynamically -->
                        <div id="productRowsContainer">
                            <!-- Initial row -->
                            <div class="row product-row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="productName" class="form-label">Product Name</label>
                                        <input type="text" class="form-control" id="productName" name="productName[]">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label for="buyingPrice" class="form-label">Buy Price</label>
                                        <input type="number" step="any" class="form-control" id="buyingPrice"
                                            name="buyingPrice[]">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label for="sellingPrice" class="form-label">Sell Price</label>
                                        <input type="number" step="any" class="form-control" name="sellingPrice[]">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label for="quantity" class="form-label">Quantity</label>
                                        <input type="number" step="any" class="form-control" name="quantity[]">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label for="alertLevel" class="form-label">Alert Level</label>
                                        <input type="number" step="any" class="form-control" name="alertLevel[]">
                                    </div>
                                </div>
                                <div class="col-md-1 align-self-center">
                                    <button type="button" class="btn btn-sm btn-danger remove-row-btn">&times;</button>
                                </div>
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-success" id="addRowBtn">+ Rows</button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="saveBtn" class="btn btn-primary">Save Products</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>