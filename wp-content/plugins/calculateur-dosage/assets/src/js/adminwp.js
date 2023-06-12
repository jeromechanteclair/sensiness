import ClassicEditor from  '@ckeditor/ckeditor5-build-classic' ;
const editors = document.querySelectorAll( '.editor' );
editors.forEach(element => {
 
       ClassicEditor
        .create( element,{
            toolbar: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList',  ],

        } )
        .catch( error => {
            console.error( error );
        } );
});


 