/**
 * Admin Widget Fix Script
 * Fixes width issues for preview widgets in admin panel
 */

(function($) {
    'use strict';

    function limitPreviewWidgetWidth() {
        $('.ti-preview-box .preview .ti-widget[data-layout-category*="slider"], .preview .ti-widget[data-layout-category*="slider"]').each(function() {
            var widget = $(this);
            var previewContainer = widget.closest('.preview, .ti-preview-box');

            if (previewContainer.length > 0) {
                var containerWidth = previewContainer.width();

                if (containerWidth > 0 && containerWidth < 1200) {
                    // Limit widget width to container width
                    widget.css('max-width', containerWidth + 'px');

                    // Resize slider after width change
                    if (widget[0].TrustindexWidget && widget[0].TrustindexWidget.resize) {
                        setTimeout(function() {
                            widget[0].TrustindexWidget.resize(true);
                        }, 100);
                    }
                }
            }
        });
    }

    function removeWidgetWidth() {
        $('.ti-widget[style*="width"]:not([data-layout-category*="slider"])').each(function() {
            var widget = $(this);
            var style = widget.attr('style');

            if (style && style.indexOf('width') !== -1) {
                // Remove width from style or clear style completely if only width
                style = style.replace(/width\s*:\s*[^;]+;?/gi, '').trim();

                if (style === '' || style === ';') {
                    widget.removeAttr('style');
                } else {
                    widget.attr('style', style);
                }
            }
        });
    }

    // Execute on document ready and after widget initialization
    $(document).ready(function() {
        setTimeout(limitPreviewWidgetWidth, 500);
        setTimeout(limitPreviewWidgetWidth, 1500);
        setTimeout(removeWidgetWidth, 500);
        setTimeout(removeWidgetWidth, 1500);
    });

    // Also listen for widget initialization events
    if (window.TrustindexWidget) {
        var originalInit = window.TrustindexWidget.initWidgetsFromDom;
        if (originalInit) {
            window.TrustindexWidget.initWidgetsFromDom = function() {
                originalInit.apply(this, arguments);
                setTimeout(limitPreviewWidgetWidth, 200);
                setTimeout(removeWidgetWidth, 200);
            };
        }
    }

})(jQuery);