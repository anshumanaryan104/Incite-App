import 'dart:async';
import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter_pdfview/flutter_pdfview.dart';
import 'package:image_picker/image_picker.dart';
import 'package:incite/api_controller/user_controller.dart';
import 'package:incite/urls/url.dart';
import 'package:incite/utils/image_util.dart';
import 'package:incite/utils/theme_util.dart';
import 'package:incite/widgets/back.dart';
import 'package:incite/widgets/loader.dart';
import 'package:incite/widgets/svg_icon.dart';
import 'package:path_provider/path_provider.dart';
import '../model/news.dart';
import 'package:http/http.dart' as http;

import '../pages/main/widgets/share.dart';

class PdfViewWidget extends StatefulWidget {
  const PdfViewWidget({super.key, this.model});

  final ENews? model;

  @override
  State<PdfViewWidget> createState() => _PdfViewWidgetState();
}

class _PdfViewWidgetState extends State<PdfViewWidget> {
  int? pages;
  String remotePDFpath = "";
  bool isReady = false;
  bool isLoading = true;

  bool isShare = false;

  Future<String> createFileOfPdfUrl() async {
    final Directory tempDir = await getTemporaryDirectory();
    final String tempPath = tempDir.path;
    final String pdfPath = '$tempPath/sample.pdf';
    final response = await http.get(Uri.parse(widget.model!.pdf));

    final file = File(pdfPath);
    await file.writeAsBytes(response.bodyBytes);

    return pdfPath;
  }

  final Completer<PDFViewController> _controller = Completer<PDFViewController>();
  PDFViewController? pdfViewController;

  @override
  void initState() {
    createFileOfPdfUrl().then((value) {
      setState(() {
        remotePDFpath = value;
      });
    });
    super.initState();
  }

  int curr = 0;
  bool isShow = true;

  @override
  Widget build(BuildContext context) {
    return remotePDFpath == ''
        ? Material(
            color: Theme.of(context).scaffoldBackgroundColor,
            child: const Center(
              child: CircularProgressIndicator(),
            ),
          )
        : CustomLoader(
            isLoading: isShare,
            child: Scaffold(
              body: Column(
                children: [
                  if (isShow == true)
                    Container(
                        width: size(context).width,
                        padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 16),
                        color: Theme.of(context).appBarTheme.backgroundColor,
                        child: SafeArea(
                          child: Row(
                            children: [
                              const Backbut(),
                              const SizedBox(width: 16),
                              Text(widget.model!.name.toString(),
                                  style: Theme.of(context)
                                      .textTheme
                                      .displaySmall!
                                      .copyWith(fontWeight: FontWeight.w700)),
                              const Spacer(),
                              InkResponse(
                                onTap: () async {
                                  isShare = true;
                                  setState(() {});
                                  await downloadImage(widget.model!.image ?? allSettings.value.appLogo ?? "")
                                      .then((image) async {
                                    shareImage(
                                        image ?? XFile(''), "${Urls.baseServer}e-news/${widget.model!.id}");
                                    isShare = false;
                                    setState(() {});
                                  });
                                },
                                child: CircleAvatar(
                                  backgroundColor: Theme.of(context).cardColor,
                                  child: SvgIcon(SvgImg.share,
                                      color: dark(context) ? Colors.white : Colors.black),
                                ),
                              )
                            ],
                          ),
                        )),
                  Expanded(
                    flex: 2,
                    child: Stack(
                      children: [
                        PDFView(
                          key: ValueKey(_controller),
                          filePath: remotePDFpath,
                          enableSwipe: true,
                          nightMode: false,
                          swipeHorizontal: true,
                          defaultPage: curr,
                          autoSpacing: false,
                          pageFling: true,
                          onRender: (pages) {
                            setState(() {
                              pages = pages;
                              isReady = true;
                            });
                          },
                          onError: (error) {
                            debugPrint(error.toString());
                          },
                          onPageError: (page, error) {
                            debugPrint('$page: ${error.toString()}');
                          },
                          onViewCreated: (PDFViewController pdfViewController) {
                            _controller.complete(pdfViewController);
                            pdfViewController = pdfViewController;
                            setState(() {});
                          },
                          pageSnap: false,
                          onPageChanged: (int? page, int? total) {
                            curr = page!.toInt();
                            pages = total!.toInt();
                            setState(() {});
                            debugPrint('page change: $page/$total');
                          },
                        ),
                      ],
                    ),
                  ),
                  if (isShow == true)
                    FutureBuilder<PDFViewController>(
                      future: _controller.future,
                      builder: (context, AsyncSnapshot<PDFViewController> snapshot) {
                        if (snapshot.hasData) {
                          return Container(
                            color: Theme.of(context).cardColor,
                            padding: const EdgeInsets.symmetric(vertical: 8),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                IconButton(
                                    onPressed: () async {
                                      if (curr >= 0) {
                                        curr--;
                                        snapshot.data!.setPage(curr);
                                        setState(() {});
                                      }
                                      //  setState(() {   });
                                    },
                                    icon: const Icon(Icons.chevron_left)),
                                SizedBox(
                                  width: 100,
                                  height: 30,
                                  child: Row(
                                    children: [
                                      Text('Go To :  ', style: Theme.of(context).textTheme.titleLarge!),
                                      Expanded(
                                        child: TextField(
                                          keyboardType: TextInputType.number,
                                          decoration: InputDecoration(
                                              border: OutlineInputBorder(
                                                  borderSide:
                                                      BorderSide(width: 1, color: Colors.grey.shade500)),
                                              focusedBorder: OutlineInputBorder(
                                                  borderSide: BorderSide(
                                                      width: 1, color: Theme.of(context).primaryColor)),
                                              contentPadding: const EdgeInsets.symmetric(horizontal: 4)),
                                          onChanged: (value) {
                                            if (value.isNotEmpty &&
                                                value.contains(RegExp(r'[0-9]')) &&
                                                curr < pages!.toInt() &&
                                                curr >= 0) {
                                              curr = int.parse(value) - 1;
                                              snapshot.data!.setPage(curr);
                                              setState(() {});
                                            }
                                          },
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                                Text('${curr + 1} / $pages', style: Theme.of(context).textTheme.titleLarge),
                                IconButton(
                                    onPressed: () async {
                                      if (curr < pages!.toInt()) {
                                        curr++;
                                        snapshot.data!.setPage(curr);
                                        setState(() {});
                                      }
                                      // setState(() {   });
                                    },
                                    icon: const Icon(Icons.chevron_right))
                              ],
                            ),
                          );
                        }
                        return Container();
                      },
                    ),
                ],
              ),
            ),
          );
  }
}
