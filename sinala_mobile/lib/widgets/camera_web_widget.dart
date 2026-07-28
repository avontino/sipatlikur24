export 'camera_web_widget_stub.dart'
    if (dart.library.html) 'camera_web_widget_web.dart'
    if (dart.library.io) 'camera_web_widget_mobile.dart';
