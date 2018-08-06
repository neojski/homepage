var globalWidth = window.innerWidth;
var globalHeight = window.innerHeight;

function prefStyleProp(el, name, value){
    el.style[StyleFix.camelCase(PrefixFree.prefixProperty(name))] = value;
}
function neoBox(node, dontInsert){
    var _neoBox = this;
    var lockStatus = false;
    function isLocked(){
        return lockStatus;
    }
    function lock(){
        lockStatus = true;
    }
    function unlock(){
        lockStatus = false;
    }

	function rand(min, max){
		return min + (max - min) * Math.random();
	}
	function randSgn(){
		return Math.random() > .5 ? 1 : -1;
	}

    function getShader(name){
        return neoBox.shaders[name];
    }

	function createStdCanvas(width, height, left, top){
		var canvas = document.createElement("canvas");
		canvas.width = width || imgWidth;
		canvas.height = height || imgHeight;
		canvas.style.position = "absolute";
		canvas.style.left = (left || targetPos.left) + "px";
		canvas.style.top = (top || targetPos.top) + "px";
		return canvas;
	}
    this.createGrid = function(resX, resY){
        var i, j;
        var div = document.createElement("div");
        div.className = "grid";

        div.style.width = imgWidth + "px";
        div.style.height = imgHeight + "px";
        div.style.position = "absolute";
        div.style.left = 0;
        div.style.top = 0;

        var rectWidth = Math.floor(imgWidth / resX);
        var rectHeight = Math.floor(imgHeight / resY);

        var rects = [];

        for(i = 0; i < resY; i++){
            for(j = 0; j < resX; j++){
                var rect = document.createElement("div");
                rect.className = "gridRect";

                rect.style.width = rectWidth + "px";
                rect.style.height = rectHeight + "px";
                rect.style.cssFloat = "left";

                rect.style.background = "url(" + imgSrc + ") " + -rectWidth * j + "px " + -rectHeight * i + "px";

                div.appendChild(rect);
                rects.push(rect);
            }
        }
        return div;
    }
	function postClose(){
		_neoBox.container.parentNode.removeChild(_neoBox.container);
	}
    function animNext(){
        animSwitch(1);
    }
    function animPrev(){
        animSwitch(-1);
    }
    function animSwitch(dir){
        if(!isLocked()){
            lock();
            _neoBox[currentEffect["switch"]+"Switch"](dir);
        }
    }
    function animClose(){
        if(!isLocked()){
            lock();
            _neoBox[currentEffect["close"]+"Close"]();
        }
    }
    function getInitPos(){
        var offset = $(node).offset();
        return {
            left: offset.left - $(document).scrollLeft(),
            top: offset.top - $(document).scrollTop()
        };
    }
    function getInitFullSizePos(){
        var pos = getInitPos();
        return {
            left: pos.left + width / 2 * scale - width / 2,
            top: pos.top + height / 2 * scale - height / 2
        };
    }
    function getInitImgCenter(){
        var pos = getInitPos();
        return {
            left: pos.left + imgWidth / 2 * scale,
            top: pos.top + imgHeight / 2 * scale
        };
    }
				
	var docWidth = globalWidth;
	var docHeight = globalHeight;
	
	var width = 800;
	var height = 375;
				
	var scale = .4;
				
	var imgWidth = 600;
	var imgHeight = 375;
				
	var perspective = 600;

	var targetPos = {
		left: docWidth / 2 - width / 2,
		top: docHeight / 2 - height / 2
	}
				
	var imgSrc = node.getElementsByTagName("img")[0].src;
    this.imgSrc = imgSrc;

	this.container = document.createElement("div");
	this.container.className = "neoGalleryBoxContainer";
	
	// create box
	var box = document.createElement("figure");
	box.className = "neoGalleryBox";
	this.container.appendChild(box);
	this.box = box;
	
	// create figure 
	var figure = document.createElement("figure");
	figure.className = "image";
	var img = document.createElement("img");
    this.img = img;
	img.src = imgSrc;
	figure.appendChild(img);
	box.appendChild(figure);
	
	// create rest
	var rest = document.createElement("div");
    this.rest = rest;
	rest.className = "rest";
	box.appendChild(rest);
	
	// right box
	var boxRight = document.createElement("figcaption");
    this.boxRight = boxRight;
	boxRight.className = "right";
	rest.appendChild(boxRight);
	boxRight.innerHTML = node.getElementsByTagName("figcaption")[1].innerHTML;
	
	// bottom box
	var boxBottom = document.createElement("figcaption");
    this.boxBottom = boxBottom;
	boxBottom.className = "bottom";
	boxBottom.innerHTML = node.getElementsByTagName("figcaption")[0].innerHTML;
	rest.appendChild(boxBottom);
	
	// controls
	var controls = document.createElement("div");
    this.controls = controls;
	controls.className = "controls";
	rest.appendChild(controls);

	var prevButton = document.createElement("div");
    this.prevButton = prevButton;
	prevButton.className = "prev";
	prevButton.innerHTML = '<img src="resources/prev.png" />';
	controls.appendChild(prevButton);
    prevButton.onclick = animPrev;
	
	var nextButton = document.createElement("div");
    this.nextButton = nextButton;
	nextButton.className = "next";
	nextButton.innerHTML = '<img src="resources/next.png" />';
	controls.appendChild(nextButton);
    nextButton.onclick = animNext;

	var closeButton = document.createElement("div");
    this.closeButton = closeButton;
	closeButton.className = "close";
	closeButton.innerHTML = '<img src="resources/close.png" />';
	controls.appendChild(closeButton);
    closeButton.onclick = animClose;

    if(!dontInsert){
	    document.body.appendChild(this.container);
    }
	// end of box creation

    this.universalCloudAnim = function(cloud, targetOpacity){
        var canvas = createStdCanvas();
        var gl = new WebGLFrame(canvas, getShader("alpha"));
        gl.loadTexture(imgSrc);
        gl.loadTexture(cloud);

        _neoBox.container.appendChild(canvas);
        box.style.position = "absolute";
        box.style.left = targetPos.left + "px";
        box.style.top = targetPos.top + "px";
        box.style.zIndex = 1000;
        box.style.opacity = 1 - targetOpacity;
        prefStyleProp(box, "transition", "1s");

        setTimeout(function(){
            box.style.opacity = targetOpacity;
        }, 40);
        lock();

        var timer = new WebGLFrame.timer();
        var time = targetOpacity;
        function loop(){
            img.style.display = "none";

            if(time >= 0 && time <= 1){
                if(targetOpacity == 1){
                    time = 1 - timer.get() * .0005;
                }else{
                    time = timer.get() * .0005;
                }

                gl.setUniform1f("time", time);
                gl.render();
                requestAnimFrame(loop);
            }else{
                canvas.parentNode.removeChild(canvas);
                img.style.display = "block";

                unlock();

                if(targetOpacity == 0){
                    postClose();
                }
                box.style.zIndex = "auto";
            }
        }
        loop();
    }

    // open effects
	this.instantOpen = function(){
		box.style.position = "absolute";
		box.style.left = targetPos.left + "px";
		box.style.top = targetPos.top + "px";
	}
	this.throwOpen = function(){
        lock();
		var transition = .3;
		var margin = .2;
					
		var vecX = docWidth / 2 - 80 - getInitImgCenter().left;
		var vecY = docHeight / 2 - getInitImgCenter().top;

//        var mark = document.createElement("div");
//        mark.style.position = "absolute";
//        mark.style.background = "red";
//        mark.style.left = getInitImgCenter().left + "px";
//        mark.style.top = getInitImgCenter().top + "px";
//        mark.style.width = "5px";
//        mark.style.height = "5px";
//        document.body.appendChild(mark);
					
		box.style.position = "absolute";
		box.style.left = getInitFullSizePos().left + "px";
		box.style.top = getInitFullSizePos().top + "px";

        prefStyleProp(box, "transform", "scale(" + scale + ")");
		rest.style.opacity = "0";
				
		// some cache and it's done without transition
		setTimeout(function(){
            prefStyleProp(rest, "transition", "1s");
            prefStyleProp(box, "transition", transition + "s ease-in");
            prefStyleProp(box, "transform", "scale(1)");

            box.style.left = targetPos.left + "px";
            box.style.top = targetPos.top + "px";
		}, 40);
					
		var canvas = createStdCanvas(
			imgWidth * (1 + margin),
			imgHeight * (1 + margin), 
			targetPos.left - margin * imgWidth / 2,
			targetPos.top - margin * imgHeight / 2
			);
					
		var gl = new WebGLFrame(canvas, getShader("throw"));
		gl.loadTexture(imgSrc);

		// transitionEnd
		setTimeout(function(){
			_neoBox.container.appendChild(canvas);
			img.style.display = "none";
						
			var timer = new WebGLFrame.timer();
			var time = 0;
			function loop(){
				if(time < 1){
					time = timer.get() * .002 * (1 - transition);

					gl.setUniform1f("time", time);
					gl.setUniform1f("vecX", vecX * .0006);
					gl.setUniform1f("vecY", -vecY * .0006);
					gl.setUniform1f("margin", margin);

					gl.render();
					requestAnimFrame(loop);
				}else{
                    unlock();
					img.style.display = "block";
					canvas.parentNode.removeChild(canvas);
					rest.style.opacity = "1";
				}
			}
			loop();
						
		}, transition * 1e3);
	}
	this.unfoldOpen = function(){
        lock();

		box.style.position = "absolute";
		box.style.left = getInitFullSizePos().left + "px";
		box.style.top = getInitFullSizePos().top + "px";

		prefStyleProp(box, "transform", "scale(" + scale + ")");
        prefStyleProp(boxRight, "backface-visibility", "hidden");
		prefStyleProp(boxRight, "transform", "rotateY(180deg)");
        prefStyleProp(boxRight, "transform-origin", "left");
		prefStyleProp(boxBottom, "transform", "rotateX(-270deg)");
        prefStyleProp(boxBottom, "transform-origin", "bottom");
        controls.style.opacity = 0;



		// some cache and it's done without transition
		setTimeout(function(){
            prefStyleProp(box, "transition", "1s");
            prefStyleProp(boxRight, "transition", "1s");
            prefStyleProp(boxBottom, "transition", "1s");
            prefStyleProp(controls, "transition", "1s");

			box.style.left = targetPos.left + "px";
			box.style.top = targetPos.top + "px";

			prefStyleProp(box, "transform", "scale(1)");
		}, 40);

		// unfold after end of trasition
		setTimeout(function(){
            unlock();

			prefStyleProp(boxRight, "transform", "rotateY(0deg)");
			prefStyleProp(boxBottom, "transform", "rotateX(0deg)");
            controls.style.opacity = 1;
		}, 1e3);
	}
    this.cloudOpen = function(){
        _neoBox.universalCloudAnim("resources/cloud.jpg", 1);
    }
    this.chaosOpen = function(){
        lock();
        var rotation = 700;
        var minZ = -400;
        var maxZ = 400;
        var transition = .5;
        var maxDelay = .7;

        var rect;
        var grid = _neoBox.createGrid(6, 6);
        box.appendChild(grid);

        box.style.position = "absolute";
        box.style.left = targetPos.left + "px";
        box.style.top = targetPos.top + "px";

        img.style.display = "none";

        controls.style.opacity = 0;
        boxBottom.style.opacity = 0;
        prefStyleProp(boxBottom, "transition", "1s");
        prefStyleProp(controls, "transition", "1s");


        for(rect = grid.firstChild; rect; rect = rect.nextSibling){
            prefStyleProp(rect, "transform", "translate3d(0, 0, " + rand(minZ, maxZ) + "px) rotateX(" + rand(-rotation, rotation) + "deg)");
            prefStyleProp(rect, "transition", transition + "s " + rand(0, maxDelay) + "s");
            rect.style.opacity = 0;
        }
        prefStyleProp(boxRight, "transform", "translate3d(0, 0, " + rand(minZ, maxZ) + "px) rotateX(" + rand(-rotation, rotation) + "deg)");
        prefStyleProp(boxRight, "transition", transition + "s " + rand(0, maxDelay) + "s");
        boxRight.style.opacity = 0;


        setTimeout(function(){
            for(rect = grid.firstChild; rect; rect = rect.nextSibling){
                prefStyleProp(rect, "transform", "scale(1)");
                rect.style.opacity = 1;
            }

            prefStyleProp(boxRight, "transform", "scale(1)");
            boxRight.style.opacity = 1;

            setTimeout(function(){
                unlock();
                controls.style.opacity = 1;
                boxBottom.style.opacity = 1;

                img.style.display = "block";
                grid.parentNode.removeChild(grid);
            }, (transition + maxDelay) * 1e3);
        }, 40);
    }
    this.hellixOpen = function(){
        lock();

        var rotation = 200;
        var transition = 1;
        var gridSize = 20;
        var delay = .05;

        var rect;
        var grid = _neoBox.createGrid(gridSize, 1);
        box.appendChild(grid);

        box.style.position = "absolute";
        box.style.left = targetPos.left + "px";
        box.style.top = targetPos.top + "px";

        img.style.display = "none";

        controls.style.opacity = 0;
        boxBottom.style.opacity = 0;


        var i = 0;
        for(rect = grid.firstChild; rect; rect = rect.nextSibling){
            prefStyleProp(rect, "transform", "rotateX(" + rotation + "deg)");
            prefStyleProp(rect, "transition", transition + "s " + i + "s");
            rect.style.opacity = 0;
            i += delay;
        }

        prefStyleProp(boxRight, "transform", "rotateX(" + rotation + "deg)");
        prefStyleProp(boxRight, "transition", transition + "s " + gridSize * delay  + "s");
        boxRight.style.opacity = 0;

        prefStyleProp(boxBottom, "transition", "1s");
        prefStyleProp(controls, "transition", "1s");


        setTimeout(function(){
            for(rect = grid.firstChild; rect; rect = rect.nextSibling){
                prefStyleProp(rect, "transform", "rotateX(0deg)");
                rect.style.opacity = 1;
            }

            prefStyleProp(boxRight, "transform", "rotateX(0deg)");
            boxRight.style.opacity = 1;

            setTimeout(function(){
                unlock();
                controls.style.opacity = 1;
                boxBottom.style.opacity = 1;

                img.style.display = "block";
                grid.parentNode.removeChild(grid);
            }, (transition + gridSize * delay) * 1e3);
        }, 40);
    }

    // close effects
    this.explodeClose = function(){
        var rect;
        var grid = _neoBox.createGrid(5, 5);
        box.appendChild(grid);

        img.parentNode.removeChild(img);
        rest.style.zIndex = 100;


        for(rect = grid.firstChild; rect; rect = rect.nextSibling){
            prefStyleProp(rect, "transition", "2s ease-out");
        }
        prefStyleProp(boxRight, "transition", "1s");
        prefStyleProp(boxBottom, "transition", "1s");

        setTimeout(function(){
            controls.style.opacity = 0;

            for(rect = grid.firstChild; rect; rect = rect.nextSibling){
                prefStyleProp(rect, "transform", "scale(" + rand(.5, 2)+") translate(" + rand(-50, 50) + "px) rotateX(" + rand(-50, 50)+"deg) rotateY(" + rand(-50, 50) + "deg) rotateZ(" + rand(-50, 50) + "deg)");
                rect.style.opacity = 0;
            }
            prefStyleProp(boxBottom, "transform", "scale(" + rand(.7, 1)+") translate(" + rand(-30, 30) + "px) rotateX(" + rand(-30, 30)+"deg) rotateY(" + rand(-30, 30) + "deg) rotateZ(" + rand(-30, 30) + "deg)");
            boxBottom.style.opacity = 0;


            prefStyleProp(boxRight, "transform", "scale(" + rand(.7, 1)+") translate(" + rand(-30, 30) + "px) rotateX(" + rand(-30, 30)+"deg) rotateY(" + rand(-30, 30) + "deg) rotateZ(" + rand(-30, 30) + "deg)");
            boxRight.style.opacity = 0;

            setTimeout(function(){
                postClose();
            }, 2e3);
        }, 40);
    }
	this.fadeClose = function(){
        prefStyleProp(box, "transition", "1s");
		box.style.opacity = "0";
		setTimeout(function(){
			postClose();
		}, 1e3);
	}
    this.flatClose = function(){
        prefStyleProp(box, "transform-origin", "bottom");

        setTimeout(function(){
            prefStyleProp(box, "transition", "1s");
            prefStyleProp(box, "transform", "rotateX(90deg)");
            box.style.opacity = "0";
        }, 40);

        setTimeout(function(){
            postClose();
        }, 1e3);
    }
    this.magicklampClose = function(){
        var canvas = createStdCanvas();

        var gl = new WebGLFrame(canvas, getShader("magicklamp"));
        gl.loadTexture(imgSrc);

        _neoBox.container.appendChild(canvas);
        box.style.zIndex = 1000;

        box.style.opacity = "0";
        prefStyleProp(box, "transition", "1s");

        var timer = new WebGLFrame.timer();
        var time = 0;
        function loop(){
            img.style.display = "none";

            if(time < 1){
                time = timer.get() * .0005;

                gl.setUniform1f("time", time);
                gl.render();
                requestAnimFrame(loop);
            }else{
                postClose();
            }
        }
        loop();
    }
	this.sidekickClose = function(){
		box.style.left = rand(.2, .8) * docWidth - width / 2 + "px";
		box.style.top = rand(.2, .8) * docHeight - height / 2 + "px";
        prefStyleProp(box, "transform", "rotateZ(1000deg) scale(.1)");
        prefStyleProp(box, "transition", "1s");
		box.style.opacity = "0";
		
		setTimeout(function(){
			postClose();
		}, 1e3);
	}
    this.skewerClose = function () {
        var rect;
        var grid = this.createGrid(1, 10);
        box.appendChild(grid);

        img.parentNode.removeChild(img);
        rest.style.zIndex = 1;

        for (rect = grid.firstChild; rect; rect = rect.nextSibling) {
            prefStyleProp(rect, "transition", "2s ease");
        }

        controls.style.opacity = 0;
        setTimeout(function () {
            for (rect = grid.firstChild; rect; rect = rect.nextSibling) {
                prefStyleProp(rect, "transform", "translate(" + randSgn() * rand(200, 400) + "px) rotateX(" + rand(-3000, 3000) + "deg) ");
                prefStyleProp(rect, "transition", "1s");
                rect.style.opacity = 0;
            }
            prefStyleProp(boxBottom, "transform", "translate(" + randSgn() * rand(200, 400) + "px) rotateX(" + rand(-3000, 3000) + "deg) ");
            prefStyleProp(boxBottom, "transition", "1s");
            boxBottom.style.opacity = 0;


            prefStyleProp(boxRight, "transform", "scale(0) rotateX(90deg)");
            prefStyleProp(boxRight, "transform-origin", "bottom");
            prefStyleProp(boxRight, "transition", "1s");
            boxRight.style.opacity = 0;

            setTimeout(function () {
                postClose();
            }, 2e3);
        }, 40);
    }
    this.plasmaClose = function(){
        _neoBox.universalCloudAnim("resources/plasma.jpg", 0);
    }


    // switch effects
    this.flapSwitch = function(dir){
        _neoBox.universalRotateSwitch(dir,
            {
                start: {
                    transform: "rotateX(0deg)",
                    transformOrigin: "center bottom"
                },
                end: {
                    transform: "rotateX(90deg)"
                }
            },
            {
                start: {
                    transform: "rotateX(90deg)",
                    tranformOrigin: "center bottom",
                    transition: "1s .5s"
                },
                end: {
                    transform: "rotateX(0deg)"
                }
            }
        );
    }
    this.pageSwitch = function(dir){
        var transition = 1.5;
        function createDiv(){
            var img = document.createElement("div");
            img.style.width = imgWidth / 2 + "px";
            img.style.height = imgHeight + "px";
            img.style.position = "absolute";
            img.style.left = 0;
            img.style.topo = 0;
            pageBox.appendChild(img);
            return img;
        }

        // general new
        var neo2 = new neoBox(prevNextBox(node, dir), true);
        neo2.box.style.position = "absolute";
        neo2.box.style.left = targetPos.left + "px";
        neo2.box.style.top = targetPos.top + "px";
        neo2.controls.style.opacity = 0;
        neo2.boxBottom.style.opacity = 0;
        this.container.parentNode.insertBefore(neo2.container, this.container);

        // general current
        controls.style.display = "none";
        boxBottom.style.display = "none";
        img.style.display = "none";
        boxRight.style.opacity = 0;

        var pageBox = document.createElement("div");

        // create images
        var imgLeftA = createDiv();
        imgLeftA.style.background = "url(" + (dir > 0 ? imgSrc : neo2.imgSrc) + ")";

        var imgRightA = createDiv();
        imgRightA.style.background = "url(" + (dir > 0 ? neo2.imgSrc : imgSrc) + ")";
        prefStyleProp(imgRightA, "transform-style", "preserve-3d");
        prefStyleProp(imgRightA, "backface-visibility", "hidden");
        prefStyleProp(imgRightA, "transform-origin", "right");
        prefStyleProp(imgRightA, "transform", dir > 0 ? "rotateY(180deg)" : "rotateY(.1deg)"); // .1 is webkit-workaround: due to z-index problems
        prefStyleProp(imgRightA, "transition", transition + "s");

        var imgRightB = createDiv();
        imgRightB.style.background = "url(" + (dir > 0 ? neo2.imgSrc : imgSrc)+ ") right";
        imgRightB.style.left = imgWidth / 2 + "px";

        var imgLeftB = createDiv();
        imgLeftB.style.background = "url(" + (dir > 0 ? imgSrc : neo2.imgSrc) + ") right";
        imgLeftB.style.left = imgWidth / 2 + "px";
        prefStyleProp(imgLeftB, "transform-style", "preserve-3d");
        prefStyleProp(imgLeftB, "backface-visibility", "hidden");
        prefStyleProp(imgLeftB, "transform-origin", "left");
        prefStyleProp(imgLeftB, "transform", dir > 0 ? "rotateY(-.1deg)" : "rotateY(-180deg)"); // .1 is webkit-workaround: due to z-index problems
        prefStyleProp(imgLeftB, "transition", transition + "s");

        box.appendChild(pageBox);
        // end of images

        setTimeout(function(){
            prefStyleProp(imgLeftB, "transform", dir > 0 ? "rotateY(-180deg)" : "rotateY(0deg)");
            prefStyleProp(imgRightA, "transform", dir > 0 ? "rotateY(0deg)" : "rotateY(180deg)");

            prefStyleProp(neo2.boxRight, "transition", "1s");
            prefStyleProp(neo2.boxBottom, "transition", "1s");
            prefStyleProp(neo2.controls, "transition", "1s");

            setTimeout(function(){
                postClose();
                neo2.controls.style.opacity = 1;
                neo2.boxBottom.style.opacity = 1;
            }, transition * 1e3);
        }, 40);

    }
    this.rectanglesSwitch = function(dir){
        var rectTransiton = .05;
        var resX, resY;
        var neo2;
        var grid1, grid2;
        var rect1, rect2;
        var i;
        resX = resY = 5;

        // general
        neo2 = new neoBox(prevNextBox(node, dir));
        neo2.box.style.position = "absolute";
        neo2.box.style.left = targetPos.left + "px";
        neo2.box.style.top = targetPos.top + "px";

        // orig style
        this.img.style.display = "none";
        this.controls.style.display = "none";
        this.boxBottom.style.display = "none";
        prefStyleProp(boxRight, "transform", "rotateY(0deg)");
        prefStyleProp(boxRight, "backface-visibility", "hidden");
        prefStyleProp(boxRight, "transition", "1s");

        // new style
        neo2.img.style.display = "none";
        neo2.boxBottom.style.opacity = 0;
        neo2.controls.style.display = "none"; // we don't want to click them!
        neo2.controls.style.opacity = 0;
        prefStyleProp(neo2.boxRight, "transform", dir > 0 ? "rotateY(-180deg)" : "rotateY(180deg)");
        prefStyleProp(neo2.boxRight, "transition", "1s");
        prefStyleProp(neo2.boxRight, "backface-visibility", "hidden");
        prefStyleProp(neo2.boxBottom, "transition", "1s");
        prefStyleProp(neo2.controls, "transition", "1s");


        grid1 = this.createGrid(resX, resY);
        grid2 = neo2.createGrid(resX, resY);

        grid1.style.position = "absolute";
        grid1.style.top = 0;
        grid1.style.left = 0;
        grid2.style.position = "absolute";
        grid2.style.top = 0;
        grid2.style.left = 0;

        box.appendChild(grid1);
        box.appendChild(grid2);

        rect1 = grid1.firstChild;
        rect2 = grid2.firstChild;
        i = 0;
        for (; rect1 && rect2;) {
            prefStyleProp(rect1, "transition", "1s " + (rectTransiton * i) + "s");
            prefStyleProp(rect2, "transition", "1s " + (rectTransiton * i) + "s");
            prefStyleProp(rect2, "transform", dir > 0 ? "rotateY(-180deg)" : "rotateY(180deg)");

            prefStyleProp(rect1, "backface-visibility", "hidden");
            prefStyleProp(rect2, "backface-visibility", "hidden");

            rect1 = rect1.nextSibling;
            rect2 = rect2.nextSibling;
            i++;
        }

        setTimeout(function(){
            rect1 = grid1.firstChild;
            rect2 = grid2.firstChild;
            i = 0;
            for (; rect1 && rect2;) {
                prefStyleProp(rect1, "transform", dir > 0 ? "rotateY(180deg)" : "rotateY(-180deg)");
                prefStyleProp(rect2, "transform", "rotateY(0deg)");

                rect1 = rect1.nextSibling;
                rect2 = rect2.nextSibling;
                i++;
            }
            prefStyleProp(boxRight, "transform", dir > 0 ? "rotateY(180deg)" : "rotateY(-180deg)");
            prefStyleProp(neo2.boxRight, "transform", "rotateY(0deg)");

            setTimeout(function(){
                neo2.img.style.display = "block";
                neo2.boxBottom.style.opacity = 1;

                neo2.controls.style.display = "block";
                setTimeout(function(){
                    neo2.controls.style.opacity = 1;
                }, 40);
                postClose();
            }, resX * resY * (rectTransiton * 1e3) + 1000);
        }, 40);
    }
    this.rotateSwitch = function(dir){
        _neoBox.universalRotateSwitch(dir,
            {
                start: {
                    transform: "rotateY(0deg)",
                    transformOrigin: "center middle"
                },
                end: {
                    transform: "rotateY(" + (dir * 180) + "deg)"
                }
            },
            {
                start: {
                    transform: "rotateY(" + (dir * -180) + "deg)",
                    tranformOrigin: "center middle"
                },
                end: {
                    transform: "rotateY(0deg)"
                }
            }
        );
    }
    this.turnSwitch = function(dir){
        _neoBox.universalRotateSwitch(dir,
            {
                start: {
                    transform: "rotateY(0deg)",
                    transformOrigin: dir > 0 ? "left" : "right"
                },
                end: {
                    transform: "rotateY(" + (dir * 180) + "deg)"
                }
            },
            {
                start: {
                    transform: "rotateY(" + (dir * -180) + "deg)",
                    tranformOrigin: dir > 0 ? "right" : "left"
                },
                end: {
                    transform: "rotateY(0deg)"
                }
            }
        );
    }
    this.universalRotateSwitch = function(dir, origProp, newProp){
        var transition = 2;
        var neo2 = new neoBox(prevNextBox(node, dir));

        neo2.box.style.position = "absolute";
        neo2.box.style.left = targetPos.left + "px";
        neo2.box.style.top = targetPos.top + "px";

        prefStyleProp(neo2.box, "transform", newProp.start.transform);
        prefStyleProp(neo2.box, "transform-origin", newProp.start.tranformOrigin);
        prefStyleProp(neo2.box, "transition", newProp.start.transition || transition + "s");

        prefStyleProp(box, "transform", origProp.start.transform);
        prefStyleProp(box, "transition", origProp.start.transition || "0s");
        prefStyleProp(box, "transform-origin", origProp.start.transformOrigin);
        prefStyleProp(neo2.box, "opacity", 0);

        setTimeout(function(){
            prefStyleProp(box, "opacity", 0);
            prefStyleProp(neo2.box, "opacity", 1);
            prefStyleProp(box, "transition", transition + "s");

            prefStyleProp(box, "transform", origProp.end.transform);
            prefStyleProp(neo2.box, "transform", newProp.end.transform);

            setTimeout(function(){
                postClose();
            }, transition * 1e3);
        }, 40);
    }
    this.waveSwitch = function(dir){
        var transition = .5;
        var neo2 = new neoBox(prevNextBox(node, dir), true);
        neo2.box.style.position = "absolute";
        neo2.box.style.left = targetPos.left + "px";
        neo2.box.style.top = targetPos.top + "px";
        this.container.parentNode.insertBefore(neo2.container, this.container);

        prefStyleProp(box, "transform-origin", "center");
        setTimeout(function(){
            prefStyleProp(box, "transition", transition + "s ease-out");
            prefStyleProp(box, "transform", "scale(1.1)");
            box.style.opacity = 0;

            setTimeout(function(){
                postClose();
            }, transition * 1e3);
        }, 40);
    }
}

neoBox.effects = [
    {
        name: "open",
        list: [{name: "hellix"}, {name: "chaos"}, {name: "instant"}, {name: "unfold"}, {name: "throw", webgl: true}, {name: "cloud", webgl: true}],
        def: "chaos"
    },
    {
        name: "close",
        list: [{name: "plasma", webgl: true}, {name: "sidekick"}, {name: "flat"}, {name: "fade"}, {name: "explode"}, {name: "skewer"}, {name: "magicklamp", webgl: true}],
        def: "explode"
    },
    {
        name: "switch",
        list: [{name: "wave"}, {name: "page"}, {name: "rectangles"}, {name: "flap"}, {name: "rotate"}, {name: "turn"}],
        def: "rectangles"
    }
];

if(WebGLFrame.webglAvail){
	neoBox.shaders = {};
	var shadersName = ["magicklamp", "throw", "alpha"];
    var preloadTextures = ["resources/plasma.jpg", "resources/cloud.jpg"];
    var preloadImg;
	var i;
				
	for(i = 0; i < shadersName.length; i++){
		$.ajax({
			url: "resources/" + shadersName[i] + ".fs",
			name: shadersName[i],
			success: function(data){
				neoBox.shaders[this.name] = data;
			},
			error: function(){
				alert("Can't load shaders")
			},
			dataType: 'text'
		});
	}

    // preload clouds
    for(i = 0; i < preloadTextures.length; i++){
        preloadImg = new Image();
        preloadImg.src = preloadTextures[i];
    }
}

// quick'n'dirty
function nextBox(node){
    var next = $(node).next(".neoGallery")[0];
    if(next){
        return next;
    }else{
        return $(".neoGallery")[0];
    }
}
// quick'n'dirty
function prevBox(node){
    var prev = $(node).prev(".neoGallery")[0];
    if(prev){
        return prev;
    }else{
        return $(".neoGallery").last()[0];
    }
}
function prevNextBox(node, dir){
    return dir < 0 ? prevBox(node) : nextBox(node);
}

var currentEffect = {};
$(document).ready(function(){
    var ul
    var li;
    var i, j;
    var effect;
    var category;
    var def;

    for (i = 0; i < neoBox.effects.length; i++){
        category = neoBox.effects[i];

        ul = document.createElement("ul");
        ul.className = category.name + "Effect"

        for (j = 0; j < neoBox.effects[i].list.length; j++) {
            effect = neoBox.effects[i].list[j];

            if(category.def == effect.name){
                def = j;
            }

            li = document.createElement("li");
            li.appendChild(document.createTextNode(effect.name));

            if(effect.webgl){
                $(li).addClass("webGLEffect");
            }

            li.category = category.name;
            li.onclick = function(){
                if($(this).hasClass("unavailable")){
                    return;
                }
                $(this).parent().children().removeClass("selected");
                $(this).addClass("selected");

                currentEffect[this.category] = $(this).html();
            }

            $(ul).append(li);
        }
        $("#" + category.name + "Effects").append(ul);
        $("#" + category.name + "Effects li")[def].onclick();
    }

    $("#randomEffects").click(function(){
        var categories = $("#effects > div[id$='Effects']");
        var i, category, effects;
        for(i = 0; i < categories.length; i++){
            category = categories[i];
            effects = $(category).find("li:not([class~='unavailable'])");

            effects[Math.floor(Math.random() * effects.length)].onclick();
        }
    });

    $(".neoGallery").click(function(){
        var neo = new neoBox(this);
        neo[currentEffect["open"]+"Open"]();
    });

    if(!WebGLFrame.webglAvail){
        $(".webGLEffect").each(function(){
            $(this).addClass("unavailable");
        });
    }
});