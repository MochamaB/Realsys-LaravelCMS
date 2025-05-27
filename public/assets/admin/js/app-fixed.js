!function() {
    // Helper function to safely add event listeners
    function safeAddEventListener(selector, event, callback) {
        const element = document.querySelector(selector);
        if (element) {
            element.addEventListener(event, callback);
        }
    }

    // Helper function to safely get element by ID
    function safeGetElementById(id) {
        return document.getElementById(id);
    }

    // Helper function to safely query elements
    function safeQuerySelector(selector) {
        return document.querySelector(selector);
    }

    // Helper function to safely query all elements
    function safeQuerySelectorAll(selector) {
        const elements = document.querySelectorAll(selector);
        return elements && elements.length > 0 ? elements : null;
    }

    // Modified version of your app.js code with null checks
    var navbarMenu = safeQuerySelector(".navbar-menu");
    var navbarMenuHTML = navbarMenu ? navbarMenu.innerHTML : "";
    
    var M = 7, t = "en", a = localStorage.getItem("language");
    
    function o() {
        n(null === a ? t : a);
        var e = safeQuerySelectorAll(".language");
        e && Array.from(e).forEach(function(t) {
            t.addEventListener("click", function(e) {
                n(t.getAttribute("data-lang"));
            });
        });
    }
    
    function n(e) {
        var headerLangImg = safeGetElementById("header-lang-img");
        if (headerLangImg) {
            if (e == "en") {
                headerLangImg.src = "assets/images/flags/us.svg";
            } else if (e == "sp") {
                headerLangImg.src = "assets/images/flags/spain.svg";
            } else if (e == "gr") {
                headerLangImg.src = "assets/images/flags/germany.svg";
            } else if (e == "it") {
                headerLangImg.src = "assets/images/flags/italy.svg";
            } else if (e == "ru") {
                headerLangImg.src = "assets/images/flags/russia.svg";
            } else if (e == "ch") {
                headerLangImg.src = "assets/images/flags/china.svg";
            } else if (e == "fr") {
                headerLangImg.src = "assets/images/flags/french.svg";
            } else if (e == "ar") {
                headerLangImg.src = "assets/images/flags/ae.svg";
            }
        }
        
        localStorage.setItem("language", e);
        
        a = localStorage.getItem("language");
        if (a == null) {
            n(t);
        }
        
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "assets/lang/" + a + ".json");
        xhr.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                var a = JSON.parse(this.responseText);
                Object.keys(a).forEach(function(t) {
                    var e = safeQuerySelectorAll("[data-key='" + t + "']");
                    e && Array.from(e).forEach(function(e) {
                        e.textContent = a[t];
                    });
                });
            }
        };
        xhr.send();
    }
    
    // Fix for removeNotificationModal
    var removeNotificationModal = safeGetElementById("removeNotificationModal");
    if (removeNotificationModal) {
        removeNotificationModal.addEventListener("show.bs.modal", function(e) {
            var deleteNotification = safeGetElementById("delete-notification");
            if (deleteNotification) {
                deleteNotification.addEventListener("click", function() {
                    // Your existing code here
                });
            }
        });
    }
    
    // Fix for dropdown menu tabs
    var dropdownMenuTabs = safeQuerySelectorAll('.dropdown-menu a[data-bs-toggle="tab"]');
    if (dropdownMenuTabs) {
        Array.from(dropdownMenuTabs).forEach(function(e) {
            e.addEventListener("click", function(e) {
                e.stopPropagation();
                var tabInstance = bootstrap.Tab.getInstance(e.target);
                if (tabInstance) {
                    tabInstance.show();
                }
            });
        });
    }
    
    // Add window resize listener safely
    var q;
    window.addEventListener("resize", function() {
        if (q) {
            clearTimeout(q);
        }
        q = setTimeout(function() {
            // P() function would be defined elsewhere in your code
            if (typeof P === 'function') {
                P();
            }
        }, 2e3);
    });
    
    // Initialize the rest of your app
    o();

    // Additional initialization functions that you had in your original code
    // These should also be modified to use the safe functions
    
    function s() {
        var e = safeQuerySelectorAll(".navbar-nav .collapse");
        if (e) {
            Array.from(e).forEach(function(t) {
                var a = new bootstrap.Collapse(t, { toggle: false });
                
                t.addEventListener("show.bs.collapse", function(e) {
                    e.stopPropagation();
                    var e = t.parentElement.closest(".collapse");
                    if (e) {
                        var e = e.querySelectorAll(".collapse");
                        Array.from(e).forEach(function(e) {
                            var e = bootstrap.Collapse.getInstance(e);
                            e !== a && e.hide();
                        });
                    } else {
                        var siblings = function(e) {
                            for (var t = [], a = e.parentNode.firstChild; a; ) {
                                1 === a.nodeType && a !== e && t.push(a), a = a.nextSibling;
                            }
                            return t;
                        }(t.parentElement);
                        
                        Array.from(siblings).forEach(function(e) {
                            if (e.childNodes.length > 2) {
                                e.firstElementChild.setAttribute("aria-expanded", "false");
                            }
                            
                            var e = e.querySelectorAll("*[id]");
                            Array.from(e).forEach(function(e) {
                                e.classList.remove("show");
                                if (e.childNodes.length > 2) {
                                    var links = e.querySelectorAll("ul li a");
                                    Array.from(links).forEach(function(e) {
                                        if (e.hasAttribute("aria-expanded")) {
                                            e.setAttribute("aria-expanded", "false");
                                        }
                                    });
                                }
                            });
                        });
                    }
                });
                
                t.addEventListener("hide.bs.collapse", function(e) {
                    e.stopPropagation();
                    var e = t.querySelectorAll(".collapse");
                    Array.from(e).forEach(function(e) {
                        childCollapseInstance = bootstrap.Collapse.getInstance(e);
                        childCollapseInstance.hide();
                    });
                });
            });
        }
    }
    
    // Call remaining initialization functions
    s();
    
    // End of the main app code
}();
