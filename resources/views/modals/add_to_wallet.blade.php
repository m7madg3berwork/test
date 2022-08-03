<!-- delete Modal -->
<div id="add-to-wallet-modal" class="modal fade">
    <div class="modal-dialog modal-lg modal-dialog">
        <form action="{{ route('customers.add-wallet') }}" method="post"  enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title h6">{{translate('send money to user wallet')}}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <input type="hidden" name="user_id" id="customer-id">
                        <div class="form-group mb-3">
                            <label for="">{{ translate('Amount') }} <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" placeholder="{{ translate('Enter the amount') }}" name="amount" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="">{{ translate('Description') }}</label>
                            <textarea name="comment" id="" cols="30" rows="10" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-body text-center">
                    <p class="mt-1">{{translate('Are you sure to send money to ')}} <span id="customer-name"></span></p>
                    <button type="button" class="btn btn-link mt-2" data-dismiss="modal">{{translate('Cancel')}}</button>
                    <button type="submit" class="btn btn-primary">{{translate('Send')}}</button>
                </div>
            </div>
        </form>
    </div>
</div><!-- /.modal -->
