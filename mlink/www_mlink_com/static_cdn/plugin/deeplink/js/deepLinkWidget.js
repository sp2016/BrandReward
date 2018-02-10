define(['module', 'exports', '../js/innerFrameMessageHandler'], function (module, exports, _innerFrameMessageHandler) {
    'use strict';

    Object.defineProperty(exports, "__esModule", {
        value: true
    });

    exports.default = function (options) {
        var AUTHENTICATION_STATE_CHANGED_MESSAGE = options.isLoggedInMessage || 'logged in',
            LOGGED_OUT_MESSAGE = options.isLoggedOutMessage || 'logged out',
            url,
            innerFrameMessageHandler = (options.InnerFrameMessageHandler || _innerFrameMessageHandler2.default)();

        function paramsAsMap(paramsStr) {
            var paramArray = paramsStr.split('&'),
                i,
                param,
                shouldBeSpace = /\+/g,
                parameters = {};

            for (i = 0; i < paramArray.length; i++) {
                param = paramArray[i].split('=');
                if (param.length === 2) {
                    parameters[param[0]] = decodeURIComponent(param[1].replace(shouldBeSpace, " "));
                }
            }
            return parameters;
        }

        function urlParameters(fullUrl) {
            var indexToStartOfParameters = fullUrl.indexOf('?'),
                hasParams = indexToStartOfParameters >= 0;

            if (hasParams) {
                return paramsAsMap(fullUrl.substr(indexToStartOfParameters + 1));
            }

            return {};
        }

        function sendResized() {
            innerFrameMessageHandler.sendResizedMessageToParent();
        }

        function displayToUser(content) {
            $('#output').empty().append(content);
            sendResized();
        }

        function renderTemplate(template) {
            return $(_handlebars2.default.compile(template)());
        }

        function showLoginScreen() {
            var message, widget;
            widget = makeLoginWidget({
                success: function success() {
                    innerFrameMessageHandler.sendMessageToParent(AUTHENTICATION_STATE_CHANGED_MESSAGE);
                    fetchDeepLinks(url);
                }
            });
            message = widget.domElement();
            message.find('a').attr('target', '_blank');
            displayToUser(message);
        }

        function showAdvertiserMessageScreen() {
            var message, widget;

            widget = makeLogoutWidget({
                success: function success() {
                    innerFrameMessageHandler.sendMessageToParent(AUTHENTICATION_STATE_CHANGED_MESSAGE);
                    showLoginScreen();
                }
            });
            message = widget.domElement();
            displayToUser(message);
            innerFrameMessageHandler.sendMessageToParent(AUTHENTICATION_STATE_CHANGED_MESSAGE);
        }

        function showLinkNotFound(error) {
            var domain = function domain(data) {
                var a = document.createElement('a');
                a.href = data;
                return a.hostname;
            },
                translatedError = _CONTENT2.default.getAndReplace(error.code, [domain(url)]),
                html = renderTemplate(_deepLinkNotFound2.default);

            html.find('.data').text(translatedError);
            displayToUser(html);
        }

        function fetchDeepLinks(url) {
            deepLinkService.fetchDeepLinks(url, function (data) {
                var linkFoundWidget = makeLinkFoundWidget({
                    url: url,
                    deepLinkData: data
                });
                displayToUser(linkFoundWidget.view());
            }, function (error) {
                switch (error.responseCode) {
                    case 404:
                        showLinkNotFound(error);
                        break;
                    case 401:
                        showLoginScreen();
                        break;
                    case 403:
                        showAdvertiserMessageScreen();
                        break;
                    default:
                        displayToUser(renderTemplate(_deepLinkError2.default));
                }
            });
        }

        innerFrameMessageHandler.registerHandler(function (message) {
            return message === "log out";
        }, function (message) {
            showLoginScreen();
        });

        return {
            start: function start(value) {
                url = urlParameters(value).url;
                if (url) {
                    sendResized();
                }
            },
            setDeepLinkService: function setDeepLinkService(value) {
                deepLinkService = value;
            }
        };
    };

    var _innerFrameMessageHandler2 = _interopRequireDefault(_innerFrameMessageHandler);

    function _interopRequireDefault(obj) {
        return obj && obj.__esModule ? obj : {
            default: obj
        };
    }

    /*jshint newcap:false */
    /*global $, define*/
    ;
    module.exports = exports['default'];
});
//# sourceMappingURL=data:application/json;charset=utf8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbImphdmFzY3JpcHQvcHVibGlzaGVyL2Jvb2ttYXJrbGV0L2RlZXBMaW5rV2lkZ2V0LmpzIl0sIm5hbWVzIjpbIm9wdGlvbnMiLCJBVVRIRU5USUNBVElPTl9TVEFURV9DSEFOR0VEX01FU1NBR0UiLCJpc0xvZ2dlZEluTWVzc2FnZSIsIkxPR0dFRF9PVVRfTUVTU0FHRSIsImlzTG9nZ2VkT3V0TWVzc2FnZSIsInVybCIsImRlZXBMaW5rU2VydmljZSIsIkRlZXBMaW5rU2VydmljZSIsIm1ha2VMb2dpbldpZGdldCIsIkxvZ2luV2lkZ2V0IiwibWFrZUxvZ291dFdpZGdldCIsIkxvZ291dFdpZGdldCIsIm1ha2VMaW5rRm91bmRXaWRnZXQiLCJMaW5rRm91bmRXaWRnZXQiLCJpbm5lckZyYW1lTWVzc2FnZUhhbmRsZXIiLCJJbm5lckZyYW1lTWVzc2FnZUhhbmRsZXIiLCJyZWdpc3RlckhlbHBlciIsImtleSIsImdldCIsInBhcmFtc0FzTWFwIiwicGFyYW1zU3RyIiwicGFyYW1BcnJheSIsInNwbGl0IiwiaSIsInBhcmFtIiwic2hvdWxkQmVTcGFjZSIsInBhcmFtZXRlcnMiLCJsZW5ndGgiLCJkZWNvZGVVUklDb21wb25lbnQiLCJyZXBsYWNlIiwidXJsUGFyYW1ldGVycyIsImZ1bGxVcmwiLCJpbmRleFRvU3RhcnRPZlBhcmFtZXRlcnMiLCJpbmRleE9mIiwiaGFzUGFyYW1zIiwic3Vic3RyIiwic2VuZFJlc2l6ZWQiLCJzZW5kUmVzaXplZE1lc3NhZ2VUb1BhcmVudCIsImRpc3BsYXlUb1VzZXIiLCJjb250ZW50IiwiJCIsImVtcHR5IiwiYXBwZW5kIiwicmVuZGVyVGVtcGxhdGUiLCJ0ZW1wbGF0ZSIsImNvbXBpbGUiLCJzaG93TG9naW5TY3JlZW4iLCJtZXNzYWdlIiwid2lkZ2V0Iiwic3VjY2VzcyIsInNlbmRNZXNzYWdlVG9QYXJlbnQiLCJmZXRjaERlZXBMaW5rcyIsImRvbUVsZW1lbnQiLCJmaW5kIiwiYXR0ciIsInNob3dBZHZlcnRpc2VyTWVzc2FnZVNjcmVlbiIsInNob3dMaW5rTm90Rm91bmQiLCJlcnJvciIsImRvbWFpbiIsImRhdGEiLCJhIiwiZG9jdW1lbnQiLCJjcmVhdGVFbGVtZW50IiwiaHJlZiIsImhvc3RuYW1lIiwidHJhbnNsYXRlZEVycm9yIiwiZ2V0QW5kUmVwbGFjZSIsImNvZGUiLCJodG1sIiwidGV4dCIsImxpbmtGb3VuZFdpZGdldCIsImRlZXBMaW5rRGF0YSIsInZpZXciLCJyZXNwb25zZUNvZGUiLCJyZWdpc3RlckhhbmRsZXIiLCJzdGFydCIsInZhbHVlIiwic2V0RGVlcExpbmtTZXJ2aWNlIl0sIm1hcHBpbmdzIjoiOzs7Ozs7O3NCQVdlLFVBQVVBLE9BQVYsRUFBbUI7QUFDOUIsWUFBSUMsdUNBQXVDRCxRQUFRRSxpQkFBUixJQUE2QixXQUF4RTtBQUFBLFlBQ0lDLHFCQUFxQkgsUUFBUUksa0JBQVIsSUFBOEIsWUFEdkQ7QUFBQSxZQUVJQyxHQUZKO0FBQUEsWUFHSUMsa0JBQWtCTixRQUFRTyxlQUFSLDZCQUh0QjtBQUFBLFlBSUlDLGtCQUFrQlIsUUFBUVMsV0FBUix5QkFKdEI7QUFBQSxZQUtJQyxtQkFBbUJWLFFBQVFXLFlBQVIsMEJBTHZCO0FBQUEsWUFNSUMsc0JBQXNCWixRQUFRYSxlQUFSLDZCQU4xQjtBQUFBLFlBT0lDLDJCQUEyQixDQUFDZCxRQUFRZSx3QkFBUixzQ0FBRCxHQVAvQjs7QUFTQSw2QkFBV0MsY0FBWCxDQUEwQixXQUExQixFQUF1QyxVQUFTQyxHQUFULEVBQWM7QUFDakQsbUJBQU8sa0JBQVFDLEdBQVIsQ0FBWUQsR0FBWixLQUFvQixNQUFNQSxHQUFOLEdBQVksR0FBdkM7QUFDSCxTQUZEOztBQUlBLGlCQUFTRSxXQUFULENBQXFCQyxTQUFyQixFQUFnQztBQUM1QixnQkFBSUMsYUFBYUQsVUFBVUUsS0FBVixDQUFnQixHQUFoQixDQUFqQjtBQUFBLGdCQUNJQyxDQURKO0FBQUEsZ0JBRUlDLEtBRko7QUFBQSxnQkFHSUMsZ0JBQWdCLEtBSHBCO0FBQUEsZ0JBSUlDLGFBQWEsRUFKakI7O0FBTUEsaUJBQUtILElBQUksQ0FBVCxFQUFZQSxJQUFJRixXQUFXTSxNQUEzQixFQUFtQ0osR0FBbkMsRUFBd0M7QUFDcENDLHdCQUFRSCxXQUFXRSxDQUFYLEVBQWNELEtBQWQsQ0FBb0IsR0FBcEIsQ0FBUjtBQUNBLG9CQUFJRSxNQUFNRyxNQUFOLEtBQWlCLENBQXJCLEVBQXdCO0FBQ3BCRCwrQkFBV0YsTUFBTSxDQUFOLENBQVgsSUFBdUJJLG1CQUFtQkosTUFBTSxDQUFOLEVBQVNLLE9BQVQsQ0FBaUJKLGFBQWpCLEVBQWdDLEdBQWhDLENBQW5CLENBQXZCO0FBQ0g7QUFDSjtBQUNELG1CQUFPQyxVQUFQO0FBQ0g7O0FBRUQsaUJBQVNJLGFBQVQsQ0FBdUJDLE9BQXZCLEVBQWdDO0FBQzVCLGdCQUFJQywyQkFBMkJELFFBQVFFLE9BQVIsQ0FBZ0IsR0FBaEIsQ0FBL0I7QUFBQSxnQkFDSUMsWUFBWUYsNEJBQTRCLENBRDVDOztBQUdBLGdCQUFJRSxTQUFKLEVBQWU7QUFDWCx1QkFBT2YsWUFBWVksUUFBUUksTUFBUixDQUFlSCwyQkFBMkIsQ0FBMUMsQ0FBWixDQUFQO0FBQ0g7O0FBRUQsbUJBQU8sRUFBUDtBQUNIOztBQUVELGlCQUFTSSxXQUFULEdBQXVCO0FBQ25CdEIscUNBQXlCdUIsMEJBQXpCO0FBQ0g7O0FBRUQsaUJBQVNDLGFBQVQsQ0FBdUJDLE9BQXZCLEVBQWdDO0FBQzVCQyxjQUFFLFNBQUYsRUFBYUMsS0FBYixHQUFxQkMsTUFBckIsQ0FBNEJILE9BQTVCO0FBQ0FIO0FBQ0g7O0FBRUQsaUJBQVNPLGNBQVQsQ0FBd0JDLFFBQXhCLEVBQWtDO0FBQzlCLG1CQUFPSixFQUFFLHFCQUFXSyxPQUFYLENBQW1CRCxRQUFuQixHQUFGLENBQVA7QUFDSDs7QUFFRCxpQkFBU0UsZUFBVCxHQUEyQjtBQUN2QixnQkFBSUMsT0FBSixFQUNJQyxNQURKO0FBRUFBLHFCQUFTeEMsZ0JBQWdCO0FBQ3JCeUMseUJBQVMsbUJBQVc7QUFDaEJuQyw2Q0FBeUJvQyxtQkFBekIsQ0FBNkNqRCxvQ0FBN0M7QUFDQWtELG1DQUFlOUMsR0FBZjtBQUNIO0FBSm9CLGFBQWhCLENBQVQ7QUFNQTBDLHNCQUFVQyxPQUFPSSxVQUFQLEVBQVY7QUFDQUwsb0JBQVFNLElBQVIsQ0FBYSxHQUFiLEVBQWtCQyxJQUFsQixDQUF1QixRQUF2QixFQUFpQyxRQUFqQztBQUNBaEIsMEJBQWNTLE9BQWQ7QUFDSDs7QUFFRCxpQkFBU1EsMkJBQVQsR0FBdUM7QUFDbkMsZ0JBQUlSLE9BQUosRUFDSUMsTUFESjs7QUFHQUEscUJBQVN0QyxpQkFBaUI7QUFDdEJ1Qyx5QkFBUyxtQkFBVztBQUNoQm5DLDZDQUF5Qm9DLG1CQUF6QixDQUE2Q2pELG9DQUE3QztBQUNBNkM7QUFDSDtBQUpxQixhQUFqQixDQUFUO0FBTUFDLHNCQUFVQyxPQUFPSSxVQUFQLEVBQVY7QUFDQWQsMEJBQWNTLE9BQWQ7QUFDQWpDLHFDQUF5Qm9DLG1CQUF6QixDQUE2Q2pELG9DQUE3QztBQUNIOztBQUVELGlCQUFTdUQsZ0JBQVQsQ0FBMEJDLEtBQTFCLEVBQWlDO0FBQzdCLGdCQUFJQyxTQUFTLFNBQVRBLE1BQVMsQ0FBU0MsSUFBVCxFQUFlO0FBQ3BCLG9CQUFJQyxJQUFJQyxTQUFTQyxhQUFULENBQXVCLEdBQXZCLENBQVI7QUFDQUYsa0JBQUVHLElBQUYsR0FBU0osSUFBVDtBQUNBLHVCQUFPQyxFQUFFSSxRQUFUO0FBQ0gsYUFKTDtBQUFBLGdCQUtJQyxrQkFBa0Isa0JBQVFDLGFBQVIsQ0FBc0JULE1BQU1VLElBQTVCLEVBQWtDLENBQUNULE9BQU9yRCxHQUFQLENBQUQsQ0FBbEMsQ0FMdEI7QUFBQSxnQkFNSStELE9BQU96QiwwQ0FOWDs7QUFRQXlCLGlCQUFLZixJQUFMLENBQVUsT0FBVixFQUFtQmdCLElBQW5CLENBQXdCSixlQUF4QjtBQUNBM0IsMEJBQWM4QixJQUFkO0FBQ0g7O0FBRUQsaUJBQVNqQixjQUFULENBQXdCOUMsR0FBeEIsRUFBNkI7QUFDekJDLDRCQUFnQjZDLGNBQWhCLENBQStCOUMsR0FBL0IsRUFDSSxVQUFTc0QsSUFBVCxFQUFlO0FBQ1gsb0JBQUlXLGtCQUFrQjFELG9CQUFvQjtBQUN0Q1AseUJBQUtBLEdBRGlDO0FBRXRDa0Usa0NBQWNaO0FBRndCLGlCQUFwQixDQUF0QjtBQUlBckIsOEJBQWNnQyxnQkFBZ0JFLElBQWhCLEVBQWQ7QUFDSCxhQVBMLEVBUUksVUFBU2YsS0FBVCxFQUFnQjtBQUNaLHdCQUFRQSxNQUFNZ0IsWUFBZDtBQUNJLHlCQUFLLEdBQUw7QUFDSWpCLHlDQUFpQkMsS0FBakI7QUFDQTtBQUNKLHlCQUFLLEdBQUw7QUFDSVg7QUFDQTtBQUNKLHlCQUFLLEdBQUw7QUFDSVM7QUFDQTtBQUNKO0FBQ0lqQixzQ0FBY0ssdUNBQWQ7QUFYUjtBQWFILGFBdEJMO0FBdUJIOztBQUVEN0IsaUNBQXlCNEQsZUFBekIsQ0FBeUMsVUFBUzNCLE9BQVQsRUFBa0I7QUFDdkQsbUJBQU9BLFlBQVksU0FBbkI7QUFDSCxTQUZELEVBRUcsVUFBU0EsT0FBVCxFQUFrQjtBQUNqQkQ7QUFDSCxTQUpEOztBQU1BLGVBQU87QUFDSDZCLG1CQUFPLGVBQVNDLEtBQVQsRUFBZ0I7QUFDbkJ2RSxzQkFBTXlCLGNBQWM4QyxLQUFkLEVBQXFCdkUsR0FBM0I7QUFDQSxvQkFBSUEsR0FBSixFQUFTO0FBQ0wrQjtBQUNBZSxtQ0FBZTlDLEdBQWY7QUFDSDtBQUNKLGFBUEU7QUFRSHdFLGdDQUFvQiw0QkFBU0QsS0FBVCxFQUFnQjtBQUNoQ3RFLGtDQUFrQnNFLEtBQWxCO0FBQ0g7QUFWRSxTQUFQO0FBWUgsSzs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7QUF2SkQ7QUFDQTtBQXVKQSIsImZpbGUiOiJqYXZhc2NyaXB0L3B1Ymxpc2hlci9ib29rbWFya2xldC9kZWVwTGlua1dpZGdldC5qcyIsInNvdXJjZXNDb250ZW50IjpbIi8qanNoaW50IG5ld2NhcDpmYWxzZSAqL1xuLypnbG9iYWwgJCwgZGVmaW5lKi9cbmltcG9ydCBsaW5rTm90Rm91bmRUZW1wbGF0ZSBmcm9tICd0ZXh0IWphdmFzY3JpcHQvcHVibGlzaGVyL2Jvb2ttYXJrbGV0L2RlZXBMaW5rTm90Rm91bmQuaHRtbCc7XG5pbXBvcnQgZXJyb3JUZW1wbGF0ZSBmcm9tICd0ZXh0IWphdmFzY3JpcHQvcHVibGlzaGVyL2Jvb2ttYXJrbGV0L2RlZXBMaW5rRXJyb3IuaHRtbCc7XG5pbXBvcnQgTG9naW5XaWRnZXQgZnJvbSAnamF2YXNjcmlwdC9wdWJsaXNoZXIvYm9va21hcmtsZXQvbG9naW5XaWRnZXQnO1xuaW1wb3J0IExvZ291dFdpZGdldCBmcm9tICdqYXZhc2NyaXB0L3B1Ymxpc2hlci9ib29rbWFya2xldC9sb2dvdXRXaWRnZXQnO1xuaW1wb3J0IExpbmtGb3VuZFdpZGdldCBmcm9tICdqYXZhc2NyaXB0L3B1Ymxpc2hlci9ib29rbWFya2xldC9saW5rRm91bmRXaWRnZXQnO1xuaW1wb3J0IERlZXBMaW5rU2VydmljZSBmcm9tICdqYXZhc2NyaXB0L3B1Ymxpc2hlci9ib29rbWFya2xldC9kZWVwTGlua1NlcnZpY2UnO1xuaW1wb3J0IElubmVyRnJhbWVNZXNzYWdlSGFuZGxlciBmcm9tICdqYXZhc2NyaXB0L3B1Ymxpc2hlci9ib29rbWFya2xldC9pbm5lckZyYW1lTWVzc2FnZUhhbmRsZXInO1xuaW1wb3J0IENPTlRFTlQgZnJvbSAnQ09OVEVOVCc7XG5pbXBvcnQgaGFuZGxlYmFycyBmcm9tICdoYW5kbGViYXJzJztcbmV4cG9ydCBkZWZhdWx0IGZ1bmN0aW9uIChvcHRpb25zKSB7XG4gICAgdmFyIEFVVEhFTlRJQ0FUSU9OX1NUQVRFX0NIQU5HRURfTUVTU0FHRSA9IG9wdGlvbnMuaXNMb2dnZWRJbk1lc3NhZ2UgfHwgJ2xvZ2dlZCBpbicsXG4gICAgICAgIExPR0dFRF9PVVRfTUVTU0FHRSA9IG9wdGlvbnMuaXNMb2dnZWRPdXRNZXNzYWdlIHx8ICdsb2dnZWQgb3V0JyxcbiAgICAgICAgdXJsLFxuICAgICAgICBkZWVwTGlua1NlcnZpY2UgPSBvcHRpb25zLkRlZXBMaW5rU2VydmljZSB8fCBEZWVwTGlua1NlcnZpY2UsXG4gICAgICAgIG1ha2VMb2dpbldpZGdldCA9IG9wdGlvbnMuTG9naW5XaWRnZXQgfHwgTG9naW5XaWRnZXQsXG4gICAgICAgIG1ha2VMb2dvdXRXaWRnZXQgPSBvcHRpb25zLkxvZ291dFdpZGdldCB8fCBMb2dvdXRXaWRnZXQsXG4gICAgICAgIG1ha2VMaW5rRm91bmRXaWRnZXQgPSBvcHRpb25zLkxpbmtGb3VuZFdpZGdldCB8fCBMaW5rRm91bmRXaWRnZXQsXG4gICAgICAgIGlubmVyRnJhbWVNZXNzYWdlSGFuZGxlciA9IChvcHRpb25zLklubmVyRnJhbWVNZXNzYWdlSGFuZGxlciB8fCBJbm5lckZyYW1lTWVzc2FnZUhhbmRsZXIpKCk7XG5cbiAgICBoYW5kbGViYXJzLnJlZ2lzdGVySGVscGVyKCd0cmFuc2xhdGUnLCBmdW5jdGlvbihrZXkpIHtcbiAgICAgICAgcmV0dXJuIENPTlRFTlQuZ2V0KGtleSkgfHwgXCJbXCIgKyBrZXkgKyBcIl1cIjtcbiAgICB9KTtcblxuICAgIGZ1bmN0aW9uIHBhcmFtc0FzTWFwKHBhcmFtc1N0cikge1xuICAgICAgICB2YXIgcGFyYW1BcnJheSA9IHBhcmFtc1N0ci5zcGxpdCgnJicpLFxuICAgICAgICAgICAgaSxcbiAgICAgICAgICAgIHBhcmFtLFxuICAgICAgICAgICAgc2hvdWxkQmVTcGFjZSA9IC9cXCsvZyxcbiAgICAgICAgICAgIHBhcmFtZXRlcnMgPSB7fTtcblxuICAgICAgICBmb3IgKGkgPSAwOyBpIDwgcGFyYW1BcnJheS5sZW5ndGg7IGkrKykge1xuICAgICAgICAgICAgcGFyYW0gPSBwYXJhbUFycmF5W2ldLnNwbGl0KCc9Jyk7XG4gICAgICAgICAgICBpZiAocGFyYW0ubGVuZ3RoID09PSAyKSB7XG4gICAgICAgICAgICAgICAgcGFyYW1ldGVyc1twYXJhbVswXV0gPSBkZWNvZGVVUklDb21wb25lbnQocGFyYW1bMV0ucmVwbGFjZShzaG91bGRCZVNwYWNlLCBcIiBcIikpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9XG4gICAgICAgIHJldHVybiBwYXJhbWV0ZXJzO1xuICAgIH1cblxuICAgIGZ1bmN0aW9uIHVybFBhcmFtZXRlcnMoZnVsbFVybCkge1xuICAgICAgICB2YXIgaW5kZXhUb1N0YXJ0T2ZQYXJhbWV0ZXJzID0gZnVsbFVybC5pbmRleE9mKCc/JyksXG4gICAgICAgICAgICBoYXNQYXJhbXMgPSBpbmRleFRvU3RhcnRPZlBhcmFtZXRlcnMgPj0gMDtcblxuICAgICAgICBpZiAoaGFzUGFyYW1zKSB7XG4gICAgICAgICAgICByZXR1cm4gcGFyYW1zQXNNYXAoZnVsbFVybC5zdWJzdHIoaW5kZXhUb1N0YXJ0T2ZQYXJhbWV0ZXJzICsgMSkpO1xuICAgICAgICB9XG5cbiAgICAgICAgcmV0dXJuIHt9O1xuICAgIH1cblxuICAgIGZ1bmN0aW9uIHNlbmRSZXNpemVkKCkge1xuICAgICAgICBpbm5lckZyYW1lTWVzc2FnZUhhbmRsZXIuc2VuZFJlc2l6ZWRNZXNzYWdlVG9QYXJlbnQoKTtcbiAgICB9XG5cbiAgICBmdW5jdGlvbiBkaXNwbGF5VG9Vc2VyKGNvbnRlbnQpIHtcbiAgICAgICAgJCgnI291dHB1dCcpLmVtcHR5KCkuYXBwZW5kKGNvbnRlbnQpO1xuICAgICAgICBzZW5kUmVzaXplZCgpO1xuICAgIH1cblxuICAgIGZ1bmN0aW9uIHJlbmRlclRlbXBsYXRlKHRlbXBsYXRlKSB7XG4gICAgICAgIHJldHVybiAkKGhhbmRsZWJhcnMuY29tcGlsZSh0ZW1wbGF0ZSkoKSk7XG4gICAgfVxuXG4gICAgZnVuY3Rpb24gc2hvd0xvZ2luU2NyZWVuKCkge1xuICAgICAgICB2YXIgbWVzc2FnZSxcbiAgICAgICAgICAgIHdpZGdldDtcbiAgICAgICAgd2lkZ2V0ID0gbWFrZUxvZ2luV2lkZ2V0KHtcbiAgICAgICAgICAgIHN1Y2Nlc3M6IGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgICAgIGlubmVyRnJhbWVNZXNzYWdlSGFuZGxlci5zZW5kTWVzc2FnZVRvUGFyZW50KEFVVEhFTlRJQ0FUSU9OX1NUQVRFX0NIQU5HRURfTUVTU0FHRSk7XG4gICAgICAgICAgICAgICAgZmV0Y2hEZWVwTGlua3ModXJsKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSk7XG4gICAgICAgIG1lc3NhZ2UgPSB3aWRnZXQuZG9tRWxlbWVudCgpO1xuICAgICAgICBtZXNzYWdlLmZpbmQoJ2EnKS5hdHRyKCd0YXJnZXQnLCAnX2JsYW5rJyk7XG4gICAgICAgIGRpc3BsYXlUb1VzZXIobWVzc2FnZSk7XG4gICAgfVxuXG4gICAgZnVuY3Rpb24gc2hvd0FkdmVydGlzZXJNZXNzYWdlU2NyZWVuKCkge1xuICAgICAgICB2YXIgbWVzc2FnZSxcbiAgICAgICAgICAgIHdpZGdldDtcblxuICAgICAgICB3aWRnZXQgPSBtYWtlTG9nb3V0V2lkZ2V0KHtcbiAgICAgICAgICAgIHN1Y2Nlc3M6IGZ1bmN0aW9uKCkge1xuICAgICAgICAgICAgICAgIGlubmVyRnJhbWVNZXNzYWdlSGFuZGxlci5zZW5kTWVzc2FnZVRvUGFyZW50KEFVVEhFTlRJQ0FUSU9OX1NUQVRFX0NIQU5HRURfTUVTU0FHRSk7XG4gICAgICAgICAgICAgICAgc2hvd0xvZ2luU2NyZWVuKCk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH0pO1xuICAgICAgICBtZXNzYWdlID0gd2lkZ2V0LmRvbUVsZW1lbnQoKTtcbiAgICAgICAgZGlzcGxheVRvVXNlcihtZXNzYWdlKTtcbiAgICAgICAgaW5uZXJGcmFtZU1lc3NhZ2VIYW5kbGVyLnNlbmRNZXNzYWdlVG9QYXJlbnQoQVVUSEVOVElDQVRJT05fU1RBVEVfQ0hBTkdFRF9NRVNTQUdFKTtcbiAgICB9XG5cbiAgICBmdW5jdGlvbiBzaG93TGlua05vdEZvdW5kKGVycm9yKSB7XG4gICAgICAgIHZhciBkb21haW4gPSBmdW5jdGlvbihkYXRhKSB7XG4gICAgICAgICAgICAgICAgdmFyIGEgPSBkb2N1bWVudC5jcmVhdGVFbGVtZW50KCdhJyk7XG4gICAgICAgICAgICAgICAgYS5ocmVmID0gZGF0YTtcbiAgICAgICAgICAgICAgICByZXR1cm4gYS5ob3N0bmFtZTtcbiAgICAgICAgICAgIH0sXG4gICAgICAgICAgICB0cmFuc2xhdGVkRXJyb3IgPSBDT05URU5ULmdldEFuZFJlcGxhY2UoZXJyb3IuY29kZSwgW2RvbWFpbih1cmwpXSksXG4gICAgICAgICAgICBodG1sID0gcmVuZGVyVGVtcGxhdGUobGlua05vdEZvdW5kVGVtcGxhdGUpO1xuXG4gICAgICAgIGh0bWwuZmluZCgnLmRhdGEnKS50ZXh0KHRyYW5zbGF0ZWRFcnJvcik7XG4gICAgICAgIGRpc3BsYXlUb1VzZXIoaHRtbCk7XG4gICAgfVxuXG4gICAgZnVuY3Rpb24gZmV0Y2hEZWVwTGlua3ModXJsKSB7XG4gICAgICAgIGRlZXBMaW5rU2VydmljZS5mZXRjaERlZXBMaW5rcyh1cmwsXG4gICAgICAgICAgICBmdW5jdGlvbihkYXRhKSB7XG4gICAgICAgICAgICAgICAgdmFyIGxpbmtGb3VuZFdpZGdldCA9IG1ha2VMaW5rRm91bmRXaWRnZXQoe1xuICAgICAgICAgICAgICAgICAgICB1cmw6IHVybCxcbiAgICAgICAgICAgICAgICAgICAgZGVlcExpbmtEYXRhOiBkYXRhXG4gICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICAgICAgZGlzcGxheVRvVXNlcihsaW5rRm91bmRXaWRnZXQudmlldygpKTtcbiAgICAgICAgICAgIH0sXG4gICAgICAgICAgICBmdW5jdGlvbihlcnJvcikge1xuICAgICAgICAgICAgICAgIHN3aXRjaCAoZXJyb3IucmVzcG9uc2VDb2RlKSB7XG4gICAgICAgICAgICAgICAgICAgIGNhc2UgNDA0OlxuICAgICAgICAgICAgICAgICAgICAgICAgc2hvd0xpbmtOb3RGb3VuZChlcnJvcik7XG4gICAgICAgICAgICAgICAgICAgICAgICBicmVhaztcbiAgICAgICAgICAgICAgICAgICAgY2FzZSA0MDE6XG4gICAgICAgICAgICAgICAgICAgICAgICBzaG93TG9naW5TY3JlZW4oKTtcbiAgICAgICAgICAgICAgICAgICAgICAgIGJyZWFrO1xuICAgICAgICAgICAgICAgICAgICBjYXNlIDQwMzpcbiAgICAgICAgICAgICAgICAgICAgICAgIHNob3dBZHZlcnRpc2VyTWVzc2FnZVNjcmVlbigpO1xuICAgICAgICAgICAgICAgICAgICAgICAgYnJlYWs7XG4gICAgICAgICAgICAgICAgICAgIGRlZmF1bHQ6XG4gICAgICAgICAgICAgICAgICAgICAgICBkaXNwbGF5VG9Vc2VyKHJlbmRlclRlbXBsYXRlKGVycm9yVGVtcGxhdGUpKTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9KTtcbiAgICB9XG5cbiAgICBpbm5lckZyYW1lTWVzc2FnZUhhbmRsZXIucmVnaXN0ZXJIYW5kbGVyKGZ1bmN0aW9uKG1lc3NhZ2UpIHtcbiAgICAgICAgcmV0dXJuIG1lc3NhZ2UgPT09IFwibG9nIG91dFwiO1xuICAgIH0sIGZ1bmN0aW9uKG1lc3NhZ2UpIHtcbiAgICAgICAgc2hvd0xvZ2luU2NyZWVuKCk7XG4gICAgfSk7XG5cbiAgICByZXR1cm4ge1xuICAgICAgICBzdGFydDogZnVuY3Rpb24odmFsdWUpIHtcbiAgICAgICAgICAgIHVybCA9IHVybFBhcmFtZXRlcnModmFsdWUpLnVybDtcbiAgICAgICAgICAgIGlmICh1cmwpIHtcbiAgICAgICAgICAgICAgICBzZW5kUmVzaXplZCgpO1xuICAgICAgICAgICAgICAgIGZldGNoRGVlcExpbmtzKHVybCk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH0sXG4gICAgICAgIHNldERlZXBMaW5rU2VydmljZTogZnVuY3Rpb24odmFsdWUpIHtcbiAgICAgICAgICAgIGRlZXBMaW5rU2VydmljZSA9IHZhbHVlO1xuICAgICAgICB9XG4gICAgfTtcbn1cbjtcblxuIl19