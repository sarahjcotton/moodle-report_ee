define(['jquery', 'core/modal_factory', '<AMD module containing your new modal>'], function($, ModalFactory, Modal) {
    var trigger = $('#id_submitbuttoncustom').click;
         ModalFactory.create({type: Modal.SAVE_CANCEL}, trigger);
});
