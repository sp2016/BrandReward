/*jshint newcap:false */
/*global alert, setTimeout*/
var CJ_PUBLISHER_BOOKMARKLET = (function () {
    var baseClass = 'br-account-manager',
        animate = function (options) {
            var start = new Date(),
                intervalId,
                duration = options.duration || 400;

            intervalId = setInterval(function() {
                var timePassed = new Date() - start,
                    progress = timePassed / duration;

                if (progress > 1) {
                    progress = 1;
                }

                options.step(progress);

                if (progress === 1) {
                    clearInterval(intervalId);
                }
            }, options.delay || 10);
        };

    function BookmarkletUrl(urlWithQueryString) {
        var uri, parentPath, parameters = {};

        (function (fullUrl) {
            var paramStart = fullUrl.indexOf('?'),
                paramArray,
                i,
                param,
                matchAllPlusSymbols = /\+/g;

            parameters = {};
            if (paramStart < 0) {
                uri = fullUrl;

            }  else {
                uri = fullUrl.substr(0, paramStart);
                paramArray = fullUrl.substr(paramStart + 1).split('&');
                for (i = 0; i < paramArray.length; i++) {
                    param = paramArray[i].split('=');

                    if (param.length === 2) {
                        parameters[param[0]] = decodeURIComponent(param[1].replace(matchAllPlusSymbols, " "));
                    }
                }
            }

            parentPath = uri.substr(0, uri.lastIndexOf('/') + 1);

        }(urlWithQueryString));

        return {
            bookmarkletUrl: function () {
                return uri;
            },
            pathRelativeToBookmarklet: function (relativePath) {
                return parentPath + relativePath;
            },
            parameter: function (name) {
                return parameters[name];
            }
        };
    }

    function FileLoader(bookmarklet) {

        function createElement(filename) {
            var element,
                endsWith = function (str, suffix) {
                    return str.indexOf(suffix, str.length - suffix.length) !== -1;
                };

            if (endsWith(filename, '.js')) {
                element = {
                    dom: document.createElement("script"),
                    type: "script",
                    locator: "src",
                    filename: filename
                };
                element.dom.setAttribute("type", "text/javascript");
                element.dom.setAttribute("src", bookmarklet.pathRelativeToBookmarklet(filename));
            }
            if (endsWith(filename, '.css')) {
                element = {
                    dom: document.createElement("link"),
                    type: "link",
                    locator: "href",
                    filename: filename
                };
                element.dom.setAttribute("rel", "stylesheet");
                element.dom.setAttribute("type", "text/css");
                element.dom.setAttribute("href", bookmarklet.pathRelativeToBookmarklet(filename));
            }

            return element;
        }

        function removeElementFromDocument(element) {
            var i,
                head = document.getElementsByTagName("head")[0],
                allsuspects = head.getElementsByTagName(element.type);


            for (i = allsuspects.length; i >= 0; i--) {
                if (allsuspects[i] &&
                    allsuspects[i].getAttribute(element.locator) !== null &&
                    allsuspects[i].getAttribute(element.locator).indexOf(element.filename) >= 0) {
                    allsuspects[i].parentNode.removeChild(allsuspects[i]); //remove element by calling parentNode.removeChild()
                }
            }
        }

        function addElementToDocument(element, callback) {
            var head = document.getElementsByTagName("head")[0],
                domElement = element.dom,
                done = false; // Attach handlers for all browsers

            domElement.onload = domElement.onreadystatechange = function () {
                if (!done && (!this.readyState || this.readyState === "loaded" || this.readyState === "complete")) {
                    done = true;

                    callback();

                    // Handle memory leak in IE
                    domElement.onload = domElement.onreadystatechange = null;
                }
            };

            head.appendChild(domElement);
        }

        function unloadFile(filename) {
            var element = createElement(filename);
            removeElementFromDocument(element);
        }

        function loadFile(filename, callback) {
            var element = createElement(filename);
            removeElementFromDocument(element);
            addElementToDocument(element, callback);
        }

        function loadFiles(filenameArray, callback) {
            var filename;

            if (filenameArray.length === 0) {
                callback();
                return;
            }

            filename = filenameArray.shift();
            loadFile(filename, function () {
                loadFiles(filenameArray, callback);
            });
        }

        return {
            loadFile: loadFile,
            loadFiles: loadFiles,
            unloadFile: unloadFile
        };
    }

    function VersionChecker(bookmarklet) {
        function isLatest() {
            return bookmarklet.parameter('version') === '1';
        }

        function latestBookmarkletUrl() {
            return bookmarklet.pathRelativeToBookmarklet('publisherBookmarklet.cj');
        }

        return {
            isLatest: isLatest,
            latestBookmarkletUrl: latestBookmarkletUrl
        };
    }

    function createBookmarklet(url) {
        return BookmarkletUrl(url);
    }

    function createVersionChecker(bookmarklet) {
        return VersionChecker(bookmarklet);
    }

    function createFileLoader(bookmarklet) {
        return FileLoader(bookmarklet);
    }

    function displayDeepLink(config) {
        //getElementsByClassName available IE 9+
        if (document.getElementsByClassName(baseClass).length === 0) {
            reallyDisplayDeepLink(config);
        }
    }

    function makeElement(type, attributes, children) {
        var htmlElement = document.createElement(type),
            attributeName,
            childIndex,
            styleName;

        if (attributes) {
            for (attributeName in attributes) {
                if (attributes.hasOwnProperty(attributeName)) {
                    if (attributeName === 'style') {
                        for (styleName in attributes.style) {
                            if (attributes.style.hasOwnProperty(styleName)) {
                                htmlElement.style[styleName] = attributes.style[styleName];
                            }
                        }

                    } else if (attributeName === 'text') {
                        htmlElement.innerText = attributes[attributeName];

                    } else {
                        // according to http://www.quirksmode.org/dom/w3c_core.html#attributes
                        // incomplete in IE 7-
                        htmlElement.setAttribute(attributeName, attributes[attributeName]);
                    }
                }
            }
        }

        if (children) {
            for (childIndex = 0; childIndex < children.length; childIndex++) {
                htmlElement.appendChild(children[childIndex]);
            }
        }

        return htmlElement;
    }

    function makeDiv(attributes, children) {
        return makeElement('div', attributes, children);
    }

    function changeHeight(element, newHeight) {
        var originalHeight = element.style.height === '' ? 200 : parseInt(element.style.height, 10);

        if (originalHeight !== newHeight) {
            animate({
                step: function (delta) {
                    var tempHeight = originalHeight + (newHeight - originalHeight) * delta;
                    element.style.height = tempHeight + 'px';
                }
            });
        }
    }


    function IframeMessageHandler(contentIframe, headerIframe) {
        function messageFromIframeHandler(e) {
            var message = e.data,
                contentHeightChange = 'newHeight=',
                target = e.source === contentIframe ? headerIframe : contentIframe,
                iframeElement;

            if (message.indexOf(contentHeightChange) === 0) {
                iframeElement = document.getElementById('br-bookmarklet-content');
                changeHeight(iframeElement, parseInt(message.substring(contentHeightChange.length), 10));
            } else {
                target.postMessage(message, "*");
            }
        }

        function removeMessageListener() {
            window.removeEventListener('message', messageFromIframeHandler);
        }

        window.addEventListener('message', messageFromIframeHandler, false);

        return {
            messageFromIframeHandler: messageFromIframeHandler,
            teardown: removeMessageListener
        };
    }

    function createIframeMessageHandler(contentIframe, headerIframe) {
        return IframeMessageHandler(contentIframe, headerIframe);
    }
    
    
    function reallyDisplayDeepLink(config) {
        var contentLocation = config.bookmarklet.pathRelativeToBookmarklet('../view/publisherBookmarklet.php?url=' + encodeURIComponent(location.href)),
            headerLocation = config.bookmarklet.pathRelativeToBookmarklet('publisherBookmarkletHeader.html'),
            div, delay,
            closeDiv = makeDiv({'class': 'close-bookmarklet round-bottom',"text":"x"}
//            , [makeElement('i', {'class': 'i-br-close'})]
            ),
            contentIframe = makeElement('iframe', {scrolling: 'no', src: contentLocation, id: 'br-bookmarklet-content'}),
            headerIframe = makeElement('iframe', {scrolling: 'no', src: headerLocation, id: 'br-bookmarklet-header'}),
            content = makeDiv({'class': 'bookmarklet-content animated'}, [
                makeDiv({'class': 'br-bookmarklet round-bottom'}, [contentIframe]),
                closeDiv
            ]),
            messageHandler,
            addEventListener = config.addEventListener || window.addEventListener;

        delay = config.delay || setTimeout;
        
        
        function closeBookmarklet(){
            content.className = content.className.replace(' shown', '');
            messageHandler.teardown();

            delay(function () {
                    var parent = div.parentNode;
                    parent.removeChild(div);
                },
                415 // waiting for animation to end, then removing the bookmarklet
            );
        }
        
        closeDiv.onclick = closeBookmarklet;

        div =  makeDiv({'class': baseClass + ' bookmarklet'}, [
            makeDiv({'class': 'overlay'}),
            /*makeDiv({'class': 'header bookmarklet-header'}, [
                headerIframe
            ]),*/
            content
        ]);


        document.body.appendChild(div);
        messageHandler = createIframeMessageHandler(contentIframe.contentWindow, headerIframe.contentWindow);
        
        delay(function () {
                content.className = content.className + ' shown';
            },
            15 // delay set to nominal value to get a probable refresh between showing the header and triggering animation.
        );
        
    }

    function run(config) {
        if (config.versionChecker.isLatest()) {
            config.fileLoader.loadFiles([
                '../css/publisher-bookmarklet.css'
            ], function () {

                config.displayDeepLink({
                    bookmarklet: config.bookmarklet
                });
            });
        } else {
            config.displayToUser('time to upgrade: ' + config.versionChecker.latestBookmarkletUrl());
        }
    }

    function constructor() {
        var scripts, bookmarkletSource, bookmarklet, versionChecker, fileLoader;

        scripts = document.getElementsByTagName('script');
        bookmarkletSource = scripts[scripts.length - 1].src;
        bookmarklet = createBookmarklet(bookmarkletSource);
        versionChecker = createVersionChecker(bookmarklet);
        fileLoader = createFileLoader(bookmarklet);

        if (bookmarklet.parameter('version') !== undefined) {
            run({
                versionChecker: versionChecker,
                fileLoader: fileLoader,
                bookmarklet: bookmarklet,
                displayToUser: alert,
                displayDeepLink: displayDeepLink
            });
        }
    }

    constructor();

    return {
        createFileLoader: createFileLoader,
        createBookmarklet: createBookmarklet,
        createVersionChecker: createVersionChecker,
        run: run,
        displayDeepLink: displayDeepLink,
        createIframeMessageHandler: createIframeMessageHandler,
        setAnimate: function (fct) {
            animate = fct;
        }
    };
}());
