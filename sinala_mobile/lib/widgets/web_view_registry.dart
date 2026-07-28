import 'web_view_registry_stub.dart'
    if (dart.library.html) 'web_view_registry_web.dart' as registry;

void registerWebView(String viewId, String url) {
  registry.registerWebViewFactory(viewId, url);
}
