/**
 * ZapPopPop Popups Library: Master Bootstrap Coordinator
 * Cross-Browser Compatibility: Safari, Firefox, Chrome, Edge
 */
jQuery(document).ready(function($) {
    'use strict';

    if (window.ZapPopPopLibraryFilter) window.ZapPopPopLibraryFilter.init();
    if (window.ZapPopPopLibraryPreview) window.ZapPopPopLibraryPreview.init();
    if (window.ZapPopPopLibraryImport) window.ZapPopPopLibraryImport.init();
});
