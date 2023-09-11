function video() {
    var video = $(document).find('#video-home');
    if (video.length > 0) {
        if (video[0].readyState === 4) {
            // it's loaded
            video.prev().addClass('hide');
            video[0].play();
        }
        video.on('click', function () {
            video[0].play();
        })
    }
    document.addEventListener("DOMContentLoaded", function () {
        var lazyVideos = [].slice.call(document.querySelectorAll("video.lazy"));

        if ("IntersectionObserver" in window) {
            var lazyVideoObserver = new IntersectionObserver(function (entries, observer) {
                entries.forEach(function (video) {
                    if (video.isIntersecting) {
                        for (var source in video.target.children) {
                            var videoSource = video.target.children[source];
                            if (typeof videoSource.tagName === "string" && videoSource.tagName === "SOURCE") {
                                videoSource.src = videoSource.dataset.src;
                            }
                        }

                        video.target.load();
                        video.target.classList.remove("lazy");
                        lazyVideoObserver.unobserve(video.target);
                    }
                });
            });

            lazyVideos.forEach(function (lazyVideo) {
                lazyVideoObserver.observe(lazyVideo);
            });
        }
    });
    var videoreview = $(document).find('#video-reviews');
    if (videoreview.length > 0) {
        if (videoreview[0].readyState === 4) {
            // it's loaded
            videoreview.prev().addClass('hide');
            videoreview[0].play();
        }
        videoreview.on('click', function () {
            videoreview[0].play();
        })
    }
    document.addEventListener("DOMContentLoaded", function () {
        var lazyVideos = [].slice.call(document.querySelectorAll("video.lazy"));

        if ("IntersectionObserver" in window) {
            var lazyVideoObserver = new IntersectionObserver(function (entries, observer) {
                entries.forEach(function (video) {
                    if (video.isIntersecting) {
                        for (var source in video.target.children) {
                            var videoSource = video.target.children[source];
                            if (typeof videoSource.tagName === "string" && videoSource.tagName === "SOURCE") {
                                videoSource.src = videoSource.dataset.src;
                            }
                        }

                        video.target.load();
                        video.target.classList.remove("lazy");
                        lazyVideoObserver.unobserve(video.target);
                    }
                });
            });

            lazyVideos.forEach(function (lazyVideo) {
                lazyVideoObserver.observe(lazyVideo);
            });
        }
    });
}
export {
    video
}