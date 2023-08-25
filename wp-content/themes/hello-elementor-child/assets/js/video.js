function video() {
    var video = $(document).find('#video-home');
    if(video.length>0){

    
    if (video[0].readyState === 4) {
        // it's loaded
        video.prev().addClass('hide');
        video[0].play();
    }
    video.on('click',function(){
          video[0].play();
    })
    }
}
export {
    video
}