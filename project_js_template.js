/**_START_CANVAS_ANGULAR_HELPER_V_1**/
/**WARNING: DO NOT MODIFY BELOW THIS LINE**/

(function() {
  'use strict';

  window.optly.angular_helper = {"debug":__ANGULAR_DEBUG__,"trigger_page_view_events":__ANGULAR_PAGE_VIEWS__};
  window.optly.angular_helper.experiments = {__ANGULAR_EXPERIMENTS__};

    optly_debug("Initialising...");


  // wait until angular is ready
  var interval = setInterval(function () {
    if (window.angular !== undefined && window.angular.element(document.getElementsByTagName('body')).scope() !== undefined) {
      optly_debug("angular is initialised");
      clearInterval(interval);
      var scope = window.angular.element(document.getElementsByTagName('body')).scope();
      // listen on new page loaded
      scope.$on('$viewContentLoaded', function () {
        var page = document.URL;
        optly_debug("$viewContentLoaded with page " + page);
        var activated = false;
        // look for  manual experiments 
        for (var id in optimizely.allExperiments) {
          var experiment = optimizely.allExperiments[id];
          if ((experiment.enabled === true) && window.optly.angular_helper.experiments[id] !== undefined && window.optly.angular_helper.experiments[id] === true) {
            // check the URL targeting and activate as necessary
            for (var i = 0; i < experiment.urls.length; i++) {
              var pageURL = experiment.urls[i];
              if (pageURL.match == 'simple' && simpleMatch(page, pageURL.value)) {
                activated = true;
                optly_debug("activate (simple) " + id);
                window.optimizely.push(["activate", id]);
                break;
              } else if (pageURL.match == 'substring' && substringMatch(page, pageURL.value)) {
                activated = true;
                optly_debug("activate (substring) " + id);
                window.optimizely.push(["activate", id]);
                break;
              } else if (pageURL.match == 'exact' && exactMatch(page, pageURL.value)) {
                activated = true;
                optly_debug("activate (exact) " + id);
                window.optimizely.push(["activate", id]);
                break;
              } else if (pageURL.match == 'regex' && regexMatch(page, pageURL.value)) {
                activated = true;
                optly_debug("activate (regex) " + id);
                window.optimizely.push(["activate", id]);
                break;
              }
            }
            if (!activated) {
              optly_debug("Don't activate " + id +" on " +page);
            }
          }
        }
        if (!activated && window.optly.angular_helper.trigger_page_view_events === true) {
          // fake a pageview event. Page will be sent by default if an experiment is running
          optly_debug("pageview event " +page);
          window.optimizely.push(["trackEvent", page]);
        }
      });
    }
  }, 500);
  
  function optly_debug(msg) {
      if (window.optly.angular_helper.debug === true) {
        console.log("[canvas_angular_app] " +msg); 
      }
  }
  
  function simpleMatch(url1, url2) {
      url1 = url1.replace("http://", "").replace("https://", "").replace("www.", "");
      url1 = url1.indexOf('?') > -1 ? url1.substring(0, url1.indexOf('?')) : url1;
      url1 = url1.lastIndexOf('/') == (url1.length - 1) ? url1.substring(0, url1.lastIndexOf('/')) : url1;
      url2 = url2.replace("http://", "").replace("https://", "").replace("www.", "");
      url2 = url2.indexOf('?') > -1 ? url2.substring(0, url2.indexOf('?')) : url2;
      url2 = url2.lastIndexOf('/') == (url2.length - 1) ? url2.substring(0, url2.lastIndexOf('/')) : url2;
      return url1 == url2;
    }

    function exactMatch(url1, url2) {
      url1 = url1.replace("http://", "").replace("https://", "").replace("www.", "").replace("/?", "?");
      url1 = url1.lastIndexOf('/') == (url1.length - 1) ? url1.substring(0, url1.lastIndexOf('/')) : url1;
      url2 = url2.replace("http://", "").replace("https://", "").replace("www.", "").replace("/?", "?");
      url2 = url2.lastIndexOf('/') == (url2.length - 1) ? url2.substring(0, url2.lastIndexOf('/')) : url2;
      return url1 == url2;
    }

    function substringMatch(url1, url2) {
      url1 = url1.lastIndexOf('/') == (url1.length - 1) ? url1.substring(0, url1.lastIndexOf('/')) : url1;
      url2 = url2.lastIndexOf('/') == (url2.length - 1) ? url2.substring(0, url2.lastIndexOf('/')) : url2;
      return url1.indexOf(url2) != -1;
    }

  function regexMatch(url1, url2) {
      return url1.match(url2) !== null;
  }
})();

/**_END_CANVAS_ANGULAR_HELPER_V_1**/

