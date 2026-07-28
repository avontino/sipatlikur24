import 'dart:ui_web' as ui_web;
import 'dart:html' as html;

void registerWebViewFactory(String viewId, String url) {
  ui_web.platformViewRegistry.registerViewFactory(
    viewId,
    (int id) {
      final iframe = html.IFrameElement()
        ..src = url
        ..style.border = 'none'
        ..style.width = '100%'
        ..style.height = '100%';
      return iframe;
    },
  );
}
