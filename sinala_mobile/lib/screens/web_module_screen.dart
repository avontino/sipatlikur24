import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';
import '../widgets/web_view_registry.dart';

class WebModuleScreen extends StatefulWidget {
  final String url;
  final String title;

  const WebModuleScreen({
    super.key,
    required this.url,
    required this.title,
  });

  @override
  State<WebModuleScreen> createState() => _WebModuleScreenState();
}

class _WebModuleScreenState extends State<WebModuleScreen> {
  late final WebViewController? _controller;
  late final String _viewId;
  int _progress = 0;

  @override
  void initState() {
    super.initState();
    if (kIsWeb) {
      // Use a unique view ID based on timestamp to avoid duplicate factories
      _viewId = 'iframe_${DateTime.now().millisecondsSinceEpoch}';
      registerWebView(_viewId, widget.url);
    } else {
      _controller = WebViewController()
        ..setJavaScriptMode(JavaScriptMode.unrestricted)
        ..setBackgroundColor(const Color(0x00000000))
        ..setNavigationDelegate(
          NavigationDelegate(
            onProgress: (int progress) {
              if (mounted) {
                setState(() {
                  _progress = progress;
                });
              }
            },
            onPageStarted: (String url) {},
            onPageFinished: (String url) {},
            onWebResourceError: (WebResourceError error) {},
          ),
        )
        ..loadRequest(Uri.parse(widget.url));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.title, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 16)),
        backgroundColor: const Color(0xFF0F4C81),
        iconTheme: const IconThemeData(color: Colors.white),
        actions: kIsWeb
            ? null
            : [
                IconButton(
                  icon: const Icon(Icons.arrow_back_ios_new, size: 18),
                  tooltip: 'Kembali',
                  onPressed: () async {
                    if (await _controller!.canGoBack()) {
                      await _controller!.goBack();
                    } else {
                      if (mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Tidak ada riwayat halaman sebelumnya')),
                        );
                      }
                    }
                  },
                ),
                IconButton(
                  icon: const Icon(Icons.refresh),
                  tooltip: 'Muat Ulang',
                  onPressed: () async {
                    await _controller!.reload();
                  },
                ),
              ],
      ),
      body: Stack(
        children: [
          kIsWeb
              ? HtmlElementView(viewType: _viewId)
              : WebViewWidget(controller: _controller!),
          if (!kIsWeb && _progress < 100)
            Positioned(
              top: 0,
              left: 0,
              right: 0,
              child: LinearProgressIndicator(
                value: _progress / 100.0,
                backgroundColor: Colors.transparent,
                color: const Color(0xFF1565C0),
                minHeight: 3,
              ),
            ),
        ],
      ),
    );
  }
}
