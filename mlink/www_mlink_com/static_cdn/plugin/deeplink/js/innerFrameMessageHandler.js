define(['module', 'exports'], function (module, exports) {
    'use strict';

    Object.defineProperty(exports, "__esModule", {
        value: true
    });

    exports.default = function (options) {
        var handlers = [],
            eventPool = options && options.eventPool ? options.eventPool : window,
            parent = options && options.parent ? options.parent : window.parent;

        function registerHandler(canHandle, handle) {
            var handler = {
                canHandle: canHandle,
                handle: handle
            };

            handlers.push(handler);
        }

        function handleMessage(e) {
            var message = e.data;

            handlers.forEach(function (handler) {
                if (handler.canHandle(message)) {
                    handler.handle(message);
                }
            });
        }

        function sendMessageToParent(message) {
            parent.postMessage(message, "*");
        }

        eventPool.addEventListener('message', handleMessage, false);

        return {
            registerHandler: registerHandler,
            sendMessageToParent: sendMessageToParent,
            sendResizedMessageToParent: function sendResizedMessageToParent(height) {
                if (height === undefined) {
                    height = $('body').height();
                }
                sendMessageToParent('newHeight=' + height);
            },
            teardown: function teardown() {
                eventPool.removeEventListener('message', handleMessage);
            }
        };
    };

    ; /*global define*/
    module.exports = exports['default'];
});
//# sourceMappingURL=data:application/json;charset=utf8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbImphdmFzY3JpcHQvcHVibGlzaGVyL2Jvb2ttYXJrbGV0L2lubmVyRnJhbWVNZXNzYWdlSGFuZGxlci5qcyJdLCJuYW1lcyI6WyJvcHRpb25zIiwiaGFuZGxlcnMiLCJldmVudFBvb2wiLCJ3aW5kb3ciLCJwYXJlbnQiLCJyZWdpc3RlckhhbmRsZXIiLCJjYW5IYW5kbGUiLCJoYW5kbGUiLCJoYW5kbGVyIiwicHVzaCIsImhhbmRsZU1lc3NhZ2UiLCJlIiwibWVzc2FnZSIsImRhdGEiLCJmb3JFYWNoIiwic2VuZE1lc3NhZ2VUb1BhcmVudCIsInBvc3RNZXNzYWdlIiwiYWRkRXZlbnRMaXN0ZW5lciIsInNlbmRSZXNpemVkTWVzc2FnZVRvUGFyZW50IiwiaGVpZ2h0IiwidW5kZWZpbmVkIiwiJCIsInRlYXJkb3duIiwicmVtb3ZlRXZlbnRMaXN0ZW5lciJdLCJtYXBwaW5ncyI6Ijs7Ozs7OztzQkFHZSxVQUFVQSxPQUFWLEVBQW1CO0FBQzlCLFlBQUlDLFdBQVcsRUFBZjtBQUFBLFlBQ0lDLFlBQWFGLFdBQVdBLFFBQVFFLFNBQXBCLEdBQWlDRixRQUFRRSxTQUF6QyxHQUFxREMsTUFEckU7QUFBQSxZQUVJQyxTQUFVSixXQUFXQSxRQUFRSSxNQUFwQixHQUE4QkosUUFBUUksTUFBdEMsR0FBK0NELE9BQU9DLE1BRm5FOztBQUlBLGlCQUFTQyxlQUFULENBQXlCQyxTQUF6QixFQUFvQ0MsTUFBcEMsRUFBNEM7QUFDeEMsZ0JBQUlDLFVBQVU7QUFDVkYsMkJBQVdBLFNBREQ7QUFFVkMsd0JBQVFBO0FBRkUsYUFBZDs7QUFLQU4scUJBQVNRLElBQVQsQ0FBY0QsT0FBZDtBQUNIOztBQUVELGlCQUFTRSxhQUFULENBQXVCQyxDQUF2QixFQUEwQjtBQUN0QixnQkFBSUMsVUFBVUQsRUFBRUUsSUFBaEI7O0FBRUFaLHFCQUFTYSxPQUFULENBQWlCLFVBQVNOLE9BQVQsRUFBa0I7QUFDL0Isb0JBQUlBLFFBQVFGLFNBQVIsQ0FBa0JNLE9BQWxCLENBQUosRUFBZ0M7QUFDNUJKLDRCQUFRRCxNQUFSLENBQWVLLE9BQWY7QUFDSDtBQUNKLGFBSkQ7QUFLSDs7QUFFRCxpQkFBU0csbUJBQVQsQ0FBNkJILE9BQTdCLEVBQXNDO0FBQ2xDUixtQkFBT1ksV0FBUCxDQUFtQkosT0FBbkIsRUFBNEIsR0FBNUI7QUFDSDs7QUFFRFYsa0JBQVVlLGdCQUFWLENBQTJCLFNBQTNCLEVBQXNDUCxhQUF0QyxFQUFxRCxLQUFyRDs7QUFFQSxlQUFPO0FBQ0hMLDZCQUFpQkEsZUFEZDtBQUVIVSxpQ0FBcUJBLG1CQUZsQjtBQUdIRyx3Q0FBNEIsb0NBQVNDLE1BQVQsRUFBaUI7QUFDekMsb0JBQUlBLFdBQVdDLFNBQWYsRUFBMEI7QUFDdEJELDZCQUFTRSxFQUFFLE1BQUYsRUFBVUYsTUFBVixFQUFUO0FBQ0g7QUFDREosb0NBQW9CLGVBQWVJLE1BQW5DO0FBQ0gsYUFSRTtBQVNIRyxzQkFBVSxvQkFBVztBQUNqQnBCLDBCQUFVcUIsbUJBQVYsQ0FBOEIsU0FBOUIsRUFBeUNiLGFBQXpDO0FBQ0g7QUFYRSxTQUFQO0FBYUgsSzs7QUFDRCxLLENBL0NBIiwiZmlsZSI6ImphdmFzY3JpcHQvcHVibGlzaGVyL2Jvb2ttYXJrbGV0L2lubmVyRnJhbWVNZXNzYWdlSGFuZGxlci5qcyIsInNvdXJjZXNDb250ZW50IjpbIi8qZ2xvYmFsIGRlZmluZSovXG4vKmdsb2JhbCAkKi9cblxuZXhwb3J0IGRlZmF1bHQgZnVuY3Rpb24gKG9wdGlvbnMpIHtcbiAgICB2YXIgaGFuZGxlcnMgPSBbXSxcbiAgICAgICAgZXZlbnRQb29sID0gKG9wdGlvbnMgJiYgb3B0aW9ucy5ldmVudFBvb2wpID8gb3B0aW9ucy5ldmVudFBvb2wgOiB3aW5kb3csXG4gICAgICAgIHBhcmVudCA9IChvcHRpb25zICYmIG9wdGlvbnMucGFyZW50KSA/IG9wdGlvbnMucGFyZW50IDogd2luZG93LnBhcmVudDtcblxuICAgIGZ1bmN0aW9uIHJlZ2lzdGVySGFuZGxlcihjYW5IYW5kbGUsIGhhbmRsZSkge1xuICAgICAgICB2YXIgaGFuZGxlciA9IHtcbiAgICAgICAgICAgIGNhbkhhbmRsZTogY2FuSGFuZGxlLFxuICAgICAgICAgICAgaGFuZGxlOiBoYW5kbGVcbiAgICAgICAgfTtcblxuICAgICAgICBoYW5kbGVycy5wdXNoKGhhbmRsZXIpO1xuICAgIH1cblxuICAgIGZ1bmN0aW9uIGhhbmRsZU1lc3NhZ2UoZSkge1xuICAgICAgICB2YXIgbWVzc2FnZSA9IGUuZGF0YTtcblxuICAgICAgICBoYW5kbGVycy5mb3JFYWNoKGZ1bmN0aW9uKGhhbmRsZXIpIHtcbiAgICAgICAgICAgIGlmIChoYW5kbGVyLmNhbkhhbmRsZShtZXNzYWdlKSkge1xuICAgICAgICAgICAgICAgIGhhbmRsZXIuaGFuZGxlKG1lc3NhZ2UpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9KTtcbiAgICB9XG5cbiAgICBmdW5jdGlvbiBzZW5kTWVzc2FnZVRvUGFyZW50KG1lc3NhZ2UpIHtcbiAgICAgICAgcGFyZW50LnBvc3RNZXNzYWdlKG1lc3NhZ2UsIFwiKlwiKTtcbiAgICB9XG5cbiAgICBldmVudFBvb2wuYWRkRXZlbnRMaXN0ZW5lcignbWVzc2FnZScsIGhhbmRsZU1lc3NhZ2UsIGZhbHNlKTtcblxuICAgIHJldHVybiB7XG4gICAgICAgIHJlZ2lzdGVySGFuZGxlcjogcmVnaXN0ZXJIYW5kbGVyLFxuICAgICAgICBzZW5kTWVzc2FnZVRvUGFyZW50OiBzZW5kTWVzc2FnZVRvUGFyZW50LFxuICAgICAgICBzZW5kUmVzaXplZE1lc3NhZ2VUb1BhcmVudDogZnVuY3Rpb24oaGVpZ2h0KSB7XG4gICAgICAgICAgICBpZiAoaGVpZ2h0ID09PSB1bmRlZmluZWQpIHtcbiAgICAgICAgICAgICAgICBoZWlnaHQgPSAkKCdib2R5JykuaGVpZ2h0KCk7XG4gICAgICAgICAgICB9XG4gICAgICAgICAgICBzZW5kTWVzc2FnZVRvUGFyZW50KCduZXdIZWlnaHQ9JyArIGhlaWdodCk7XG4gICAgICAgIH0sXG4gICAgICAgIHRlYXJkb3duOiBmdW5jdGlvbigpIHtcbiAgICAgICAgICAgIGV2ZW50UG9vbC5yZW1vdmVFdmVudExpc3RlbmVyKCdtZXNzYWdlJywgaGFuZGxlTWVzc2FnZSk7XG4gICAgICAgIH1cbiAgICB9O1xufVxuO1xuIl19
