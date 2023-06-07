function file(){
    var newFileList;
    $(document).on('dragenter', 'label[for="comment_file"] ', function (e) {
		$(this).addClass('drag');
	});

	$(document).on('dragend drop dragleave', 'label[for="comment_file"]', function (e) {
		$(this).removeClass('drag');
	});

	$(document).on('change', '#comment_file', function () {

		var names = [];
		for (var i = 0; i < $(this).get(0).files.length; ++i) {
			let $placeholder = '<div class="file-drop__file" data-index="' + i + '"><i class="remove-file"></i><span class="file-drop__file__filename">' + $(this).get(0).files[i].name + '</span></div>';
			names.push();
			$($placeholder).insertAfter($('.file-drop'));
		}

	})
	$(document).on('click', '.remove-file', function (e) {

		const index = $(this).parent().attr('data-index');
		const input = document.getElementById('comment_file')
		newFileList = Array.from(input.files);
		newFileList.splice(index, 1);

		function FileListItems(files) {
			var b = new ClipboardEvent("").clipboardData || new DataTransfer()
			for (var i = 0, len = files.length; i < len; i++) b.items.add(files[i])
			return b.files
		}
		var files = new FileListItems(newFileList)
		input.files = files;

		$(this).parent().remove();

	})
}
export{
    file
}