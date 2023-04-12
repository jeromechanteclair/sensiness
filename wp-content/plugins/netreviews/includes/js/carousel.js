/*jslint devel: true */

(function () {

    "use strict";

    /**
     * Adding an event listener on every thumbnail for the modal to open and adding keyboard event binding
     * @function _init
     */
    (function _init()
    {
        addListeners();
        document.addEventListener('keydown', nrKeyboardEvent);
    })();

    /**
     * Function handling the add of event listeners on each thumbnail
     * @function addListeners
     */
    function addListeners()
    {
        document.addEventListener('click', function (event) {
            if (event.target.className === 'netreviews_image_thumb') {
                var targetElement = event.target || event.srcElement;
                openModal(targetElement);
            }
        });
    }

    /**
     * Carousel constructor
     *
     * @param {object} parent Parent div containing an <ul> list that is going to be used for carousel setup
     * @param {number} currentItem Clicked item number
     * @param {number} count Number of children in the list (<li>)
     * @constructor
     */
    function Carousel(parent, currentItem, count)
    {
        var vm = this;
        vm.element = parent;
        vm.current = currentItem;
        vm.count = count;
        vm.arrPrev = document.createTextNode('‹');
        vm.arrNext = document.createTextNode('›');
        vm.cPrevClass = 'carousel-prev';
        vm.cNextClass = 'carousel-next';
        vm.arrowClass = 'carousel-arrow';
        vm.crslClass = 'netreviews_media_part';
        vm.showPrev = showPrev;
        vm.showNext = showNext;
        vm.showArrows = showArrows;

        /**
         * Go to previous image
         * @function showPrev
         */
        function showPrev()
        {
            if (vm.current === 0) {
                vm.current = vm.count - 1;
            } else {
                vm.current = vm.current - 1;
            }
            updateModal(vm.element.querySelectorAll('.' + vm.crslClass + ' > li > a')[vm.current]);
        }

        /**
         * Go to next image
         * @function showNext
         */
        function showNext()
        {
            if (vm.current === vm.count - 1) {
                vm.current = 0;
            } else {
                vm.current = vm.current + 1;
            }
            updateModal(vm.element.querySelectorAll('.' + vm.crslClass + ' > li > a')[vm.current]);
        }

        /**
         * Create the navigation arrows (prev/next) and attach to carousel.
         * @function showArrows
         */
        function showArrows()
        {
            var modal = document.getElementById('netreviews_media_modal');

            var buttonPrev = document.createElement('a');
            buttonPrev.appendChild(vm.arrPrev);
            buttonPrev.classList.add(vm.cPrevClass);
            buttonPrev.classList.add(vm.arrowClass);
            buttonPrev.id = 'netreviews_media_prev';

            var buttonNext = document.createElement('a');
            buttonNext.appendChild(vm.arrNext);
            buttonNext.classList.add(vm.cNextClass);
            buttonNext.classList.add(vm.arrowClass);
            buttonNext.id = 'netreviews_media_next';

            // Adding event listener on arrow click
            buttonPrev.addEventListener('click', vm.showPrev);
            buttonNext.addEventListener('click', vm.showNext);

            // Adapting buttons positions
            var browserheight = document.documentElement.clientHeight;
            buttonPrev.style.top = ((browserheight - 75) / 2) + 'px';
            buttonNext.style.top = ((browserheight - 75) / 2) + 'px';

            modal.appendChild(buttonPrev);
            modal.appendChild(buttonNext);
        }
    }

    /**
     * Get current media index on click
     * @function indexInParent
     *
     * @param node
     * @returns {number} Media number
     */
    function indexInParent(node)
    {
        var children = node.parentNode.childNodes;
        var num = 0;
        for (var i = 0; i < children.length; i++) {
            if (children[i] === node) {
                return num;
            }
            if (children[i].nodeType === 1) {
                num++;
            }
        }
    }

    /**
     * Catch keyboard keydown : next / prev
     * @function nrKeyboardEvent
     */
    function nrKeyboardEvent(e)
    {
        e = e || window.event;
        var buttonPrev = document.getElementById('netreviews_media_prev');
        var buttonNext = document.getElementById('netreviews_media_next');
        var buttonClose = document.getElementById('netreviews_media_close');
        switch (e.keyCode) {
            case 37:
                if (typeof buttonPrev !== 'undefined' && buttonPrev !== null) {
                    buttonPrev.click();
                }
                break;
            case 39:
                if (typeof buttonNext !== 'undefined' && buttonNext !== null) {
                    buttonNext.click();
                }
                break;
            case 27:
                if (typeof buttonClose !== 'undefined' && buttonClose !== null) {
                    buttonClose.click();
                }
                break;
        }
    }

    /**
     * Open modal container
     * @function openModal
     */
    function openModal(event)
    {
        var identifier = event;
        var closeButton = document.getElementById("netreviews_media_close");
        var dataType = identifier.getAttribute('data-type');
        var dataSrc = identifier.getAttribute('data-src');
        var modal = document.getElementById('netreviews_media_modal');
        var parent = identifier.parentNode.parentNode;
        var count = identifier.parentNode.parentNode.querySelectorAll('li').length;
        var current = indexInParent(identifier.parentNode);
        var loader = document.createElement('div');

        loader.id = 'loader';
        loader.className = 'loader-image';
        modal.appendChild(loader);

        // Render the carousel with focus on current clicked element
        if (count > 1) {
            var carousel = new Carousel(parent, current, count);
            carousel.showArrows();
        }

        closeButton.addEventListener("click", closeModal);

        fillModal(modal, dataType, dataSrc);
    }

    /**
     * Fill modal with media info
     * @function fillModal
     *
     * @param modal
     * @param dataType
     * @param dataSrc
     */
    function fillModal(modal, dataType, dataSrc)
    {
        var modalContent = document.getElementById('netreviews_media_content');
        // Image display
        if (dataType === 'image') {
            var newImg = new Image();
            newImg.onload = function () {
                modalContent.innerHTML = "<img id='netreviews_media_image' alt='netreviews_media_image' src='" + dataSrc + "'/>";
                resizeMedia('netreviews_media_image', newImg.height, newImg.width);
                modal.style.display = 'block';
            };
            newImg.src = dataSrc;
        }
        // Video display

        else if (dataType === 'video') {
            modalContent.innerHTML = "<iframe id='netreviews_media_video' src='" + dataSrc + "'/>";
            resizeMedia('netreviews_media_video', '500', '800');
            modal.style.display = 'block';
        }
    }

    /**
     * On prev/next arrow click, update modal with media info
     * @function updateModal
     *
     * @param {Object} item An <a> tag containing source and type attribute
     */
    function updateModal(item)
    {
        var dataSrc = item.getAttribute('data-src');
        var dataType = item.getAttribute('data-type');
        var modal = document.getElementById('netreviews_media_modal');

        fillModal(modal, dataType, dataSrc);
    }

    /**
     * Close modal
     * @function closeModal
     */
    function closeModal()
    {
        var iframeNetreviews = document.getElementById('netreviews_media_iframe');
        var modal = document.getElementById('netreviews_media_modal');
        var modalContent = document.getElementById('netreviews_media_content');

        // Empty modal
        modalContent.innerHTML = '';
        removeArrows();
        removeLoader();

        modal.style.display = 'none';
        if (iframeNetreviews) {
            iframeNetreviews.setAttribute('src', '');
        }
    }

    /**
     * Remove arrows from carousel on modal close
     * @function removeArrows
     */
    function removeArrows()
    {
        var arrows = document.getElementsByClassName('carousel-arrow');
        var modal = document.getElementById('netreviews_media_modal');
        var arrayFromArrows = [];

        for (var j = 0; j < arrows.length; j++) {
            arrayFromArrows.push(arrows[j]);
        }

        arrayFromArrows.forEach(function (element) {
            modal.removeChild(element);
        });
    }

    /**
     * Remove loader on modal close
     * @function removeLoader
     */
    function removeLoader()
    {
        var modal = document.getElementById('netreviews_media_modal');
        var loader = document.getElementById('loader');
        modal.removeChild(loader);
    }

    /**
     * Resize media to fit container
     * @function resizeMedia
     *
     * @param {Object} blocId
     * @param {number} initialImgHeight
     * @param {number} initialImgWidth
     */
    function resizeMedia(blocId, initialImgHeight, initialImgWidth)
    {
        var desiredWidth;
        var desiredHeight;
        //display ratio
        var displayRatio = 0.8;
        //define image ratio
        var ratio = initialImgHeight / initialImgWidth;
        //get browser dimensions
        var browserwidth = document.documentElement.clientWidth;
        var browserheight = document.documentElement.clientHeight;
        //image plus large que haute
        if (initialImgWidth > initialImgHeight) {
            // Dimensions souhaitées
            desiredWidth = browserwidth * displayRatio;
            desiredHeight = (browserwidth * ratio) * displayRatio;
            // Si hauteur plus grande que l'ecran on adapte par la hauteur
            if (desiredHeight > browserheight) {
                desiredHeight = browserheight * displayRatio;
                desiredWidth = (browserheight / ratio) * displayRatio;
            }
        }
        //image plus haute que large
        else {
            // Dimensions souhaitées
            desiredHeight = browserheight * displayRatio;
            desiredWidth = (browserheight / ratio) * displayRatio;
            // Si largeur plus grande que l'ecran on adapte par la largeur
            if (desiredWidth > browserwidth) {
                desiredWidth = browserwidth * displayRatio;
                desiredHeight = (browserwidth * ratio) * displayRatio;
            }
        }
        // La taille maximum d'affichage est la taille réelle de l'image
        // Ne jamais zoomer une image
        if (initialImgWidth < desiredWidth && initialImgHeight < desiredHeight) {
            desiredWidth = initialImgWidth;
            desiredHeight = initialImgHeight;
        }
        // On redimensionne l'image
        document.getElementById(blocId).style.width = desiredWidth + 'px';
        document.getElementById(blocId).style.height = desiredHeight + 'px';
        // On update la position de l'image
        document.getElementById(blocId).style.left = ((browserwidth - desiredWidth) / 2) + 'px';
        document.getElementById(blocId).style.top = ((browserheight - desiredHeight) / 2) + 'px';
    }
})();