import 'dart:convert';
import 'dart:developer';
import 'dart:io';
import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:flutter_svg/svg.dart';
import 'package:get_storage/get_storage.dart';
import 'package:incite/api_controller/blog_controller.dart';
import 'package:incite/api_controller/user_controller.dart';
import 'package:incite/utils/app_theme.dart';
import 'package:incite/utils/color_util.dart';
import 'package:incite/utils/theme_util.dart';
import 'package:incite/widgets/anim_util.dart';
import 'package:incite/widgets/app_bar.dart';
import 'package:incite/widgets/custom_toast.dart';
import 'package:incite/widgets/loader.dart';
import 'package:incite/widgets/tap.dart';
import 'package:incite/widgets/text_field.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:preload_page_view/preload_page_view.dart';
import 'package:provider/provider.dart';
import 'package:speech_to_text/speech_recognition_error.dart';
import 'package:speech_to_text/speech_recognition_result.dart';
import 'package:speech_to_text/speech_to_text.dart';
import '../../api_controller/app_provider.dart';
import '../../model/blog.dart';
import '../../urls/url.dart';
import '../../utils/image_util.dart';
import 'widget/list_contain.dart';
import 'package:http/http.dart' as http;

class SearchPage extends StatefulWidget {
  const SearchPage({super.key});

  @override
  State<SearchPage> createState() => _SearchPageState();
}

class _SearchPageState extends State<SearchPage> {
  bool isRecentSearch = true;

  bool searchListShor = true;
  GetStorage localList = GetStorage();
  late TextEditingController searchController;

  FocusNode searchFocusNode = FocusNode();

  List mainDataList = [];

  bool isLoading = false;

  bool isFound = false;

  List<Blog> blogList = [];

  late AppProvider provider;

  List<LocaleName> _localeNames = [];
  double level = 0.0;
  double minSoundLevel = 50000;
  double maxSoundLevel = -50000;
  String? lastWords;
  String lastError = '';
  String lastStatus = '';
  final SpeechToText speech = SpeechToText();

  var _currentLocaleId = 'en';

  bool _onDevice = false;

  bool _hasSpeech = false;

  Future<void> initSpeechState() async {
    searchFocusNode.unfocus();
    try {
      lastWords = null;
      var hasSpeech = await speech.initialize(
        onError: errorListener,
        onStatus: statusListener,
        finalTimeout: const Duration(seconds: 6),
      );

      if (hasSpeech) {
        // Get the list of languages installed on the supporting platform so they
        // can be displayed in the UI for selection by the user.
        _localeNames = await speech.locales();

        _localeNames.forEach((e) {
          log(e.localeId.toString());
          if (e.name.contains(languageCode.value.language![0])) {
            _currentLocaleId = e.localeId;
          }
        });

        var systemLocale = await speech.systemLocale();

        _currentLocaleId = systemLocale!.localeId;
      }
      if (!mounted) return;

      _hasSpeech = hasSpeech;
      setState(() {});

      startListening();

      Future.delayed(const Duration(seconds: 6), () {
        searchListShor = true;
        isRecentSearch = false;
        isLoading = true;
        if (_hasSpeech == true) {
          stopListening();
        }
        searchContent(context);
      });
      //  await showAdaptiveDialog(context: context,
      //   useSafeArea: true,
      //   builder: (context){

      //     return SoundWidget(
      //       key: ValueKey(lastWords),
      //       words:lastWords,
      //       onTextChanged: (val){
      //         // setS
      //       },
      //     );
      //   }).then((val) async {

      //   });
    } catch (e) {
      log(e.toString());
      setState(() {
        lastError = 'Speech recognition failed: ${e.toString()}';
        _hasSpeech = false;
      });
    }
  }

  void startListening() {
    lastWords = null;
    lastError = '';
    final options = SpeechListenOptions(
        onDevice: _onDevice,
        listenMode: ListenMode.search,
        cancelOnError: true,
        partialResults: true,
        autoPunctuation: true,
        enableHapticFeedback: true);
    // Note that `listenFor` is the maximum, not the minimum, on some
    // systems recognition will be stopped before this value is reached.
    // Similarly `pauseFor` is a maximum not a minimum and may be ignored
    // on some devices.
    speech.listen(
      onResult: resultListener,
      listenFor: const Duration(seconds: 20),
      pauseFor: const Duration(seconds: 3),
      localeId: _currentLocaleId,
      onSoundLevelChange: soundLevelListener,
      listenOptions: options,
    );

    setState(() {});
  }

  void stopListening() {
    // _logEvent('stop');
    speech.stop();

    setState(() {
      level = 0.0;
      _hasSpeech = false;
      // lastWords = null;
    });
  }

  void cancelListening() {
    // _logEvent('cancel');
    speech.cancel();
    setState(() {
      isLoading = false;
      level = 0.0;
      _hasSpeech = false;
    });
  }

  /// This callback is invoked each time new recognition results are
  /// available after `listen` is called.
  void resultListener(SpeechRecognitionResult result) {
    // _logEvent(
    //     'Result listener final: ${result.finalResult}, words: ${result.recognizedWords}');

    setState(() {
      lastWords = result.recognizedWords;
      searchController.text = lastWords ?? "";
    });

    log(lastWords.toString());
  }

  void soundLevelListener(double level) {
    minSoundLevel = math.min(minSoundLevel, level);
    maxSoundLevel = math.max(maxSoundLevel, level);
    // _logEvent('sound level $level: $minSoundLevel - $maxSoundLevel ');
    setState(() {
      this.level = level;
    });
  }

  void errorListener(SpeechRecognitionError error) {
    // _logEvent(
    //     'Received error status: $error, listening: ${speech.isListening}');
    setState(() {
      lastError = '${error.errorMsg} - ${error.permanent}';
    });
  }

  void statusListener(String status) {
    if (status == 'notListening') {
      setState(() {
        stopListening();
        searchContent(context);
      });
    }

    log(lastStatus);
    log('lastStatus');
  }

  Future<void> _requestMicrophonePermission() async {
    final status = await Permission.microphone.request();

    if (status.isGranted) {
      await initSpeechState();
      // Proceed with audio functionality
    } else if (status.isDenied) {
      showCustomToast(context, "Microphone permission denied");

      openAppSettings().then((vak) async {
        await Permission.microphone.request().then((status2) async {
          if (status2.isGranted == true) {
            await initSpeechState();
            setState(() {});
          }
        });
      });
      // Handle the case when permission is denied
    } else if (status.isPermanentlyDenied) {
      // Open app settings or inform the user
      openAppSettings();
    }
  }

  @override
  void initState() {
    List local = localList.read('searchList') ?? [];
    for (int i = 0; i < local.length; i++) {
      mainDataList.add(local[i]);
    }
    provider = Provider.of<AppProvider>(context, listen: false);
    currentUser.value.isPageHome = false;
    searchController = TextEditingController();
    WidgetsBinding.instance.addPostFrameCallback((timeStamp) {
      mainDataList.reversed.toList();
      provider.getAllBookmarks();
    });
    super.initState();
  }

  PreloadPageController pageController = PreloadPageController();

  @override
  Widget build(BuildContext context) {
    return CustomLoader(
      isLoading: isLoading,
      child: Scaffold(
        body: SafeArea(
          child: CustomScrollView(
            slivers: [
              const CommonAppbar(),
              const SliverToBoxAdapter(
                child: SizedBox(height: 20),
              ),
              MediaQuery.removePadding(
                context: context,
                removeTop: true,
                removeBottom: true,
                child: SliverAppBar(
                  leadingWidth: 0,
                  automaticallyImplyLeading: false,
                  titleSpacing: 24,
                  toolbarHeight: 60,
                  pinned: true,
                  elevation: 0,
                  backgroundColor: Colors.transparent,
                  title: Hero(
                    tag: "search124",
                    child: Material(
                      color: Colors.transparent,
                      child: _hasSpeech == true
                          ? WidthAnimation(
                              key: ValueKey(_hasSpeech),
                              child: const Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(Icons.mic),
                                  SizedBox(width: 4),
                                  VoiceAnimation(),
                                ],
                              ),
                            )
                          : WidthAnimation(
                              child: TextFieldWidget(
                                radius: 30,
                                focusNode: searchFocusNode,
                                onSaved: (value) {},
                                suffix: _hasSpeech == true || searchController.text.isNotEmpty
                                    ? GestureDetector(
                                        onTap: () {
                                          if (searchController.text == '') {
                                            // showCustomToast(context, allMessages.value.searchFieldEmpty ?? 'Search Field is empty',);
                                          } else {
                                            // isNoResult = false;

                                            searchListShor = true;
                                            isRecentSearch = true;
                                            searchController.text = '';
                                            setState(() {});
                                          }
                                        },
                                        child: Padding(
                                          padding: const EdgeInsets.symmetric(horizontal: 13, vertical: 13),
                                          child: Icon(
                                              searchController.text == '' ? null : Icons.close_rounded,
                                              size: 22,
                                              color: dark(context) ? ColorUtil.white : ColorUtil.textblack),
                                        ),
                                      )
                                    : Padding(
                                        padding: const EdgeInsets.only(right: 13.0),
                                        child: TapInk(
                                          onTap: _hasSpeech ? () {} : _requestMicrophonePermission,
                                          child: const Icon(Icons.mic),
                                        )),
                                prefix: Padding(
                                  padding: const EdgeInsets.symmetric(horizontal: 13, vertical: 13),
                                  child: SvgPicture.asset(
                                    SvgImg.search,
                                    width: 22,
                                    height: 22,
                                    colorFilter: colorFilterMode(context),
                                  ),
                                ),
                                controller: searchController,
                                hint: allMessages.value.searchYourKeyword ?? 'Search your keyword',
                                textAction: TextInputAction.search,
                                onChanged: (text) {
                                  if (searchController.text.isEmpty) {
                                    isRecentSearch = true;
                                    searchListShor = true;
                                  }
                                  setState(() {});
                                },
                                onFieldSubmitted: (value) {
                                  searchListShor = true;
                                  searchContent(context);
                                },
                              ),
                            ),
                    ),
                  ),
                ),
              ),
              SliverPadding(
                padding: const EdgeInsets.only(bottom: 12, top: 10),
                sliver: SliverToBoxAdapter(
                  child: _hasSpeech == true
                      ? SoundWidget(
                          words: lastWords,
                          onTextChanged: (val) {
                            if (val == 'speak') {
                              stopListening();
                              initSpeechState();
                            } else {
                              cancelListening();
                            }
                          },
                        )
                      : isRecentSearch == true
                          ? Padding(
                              padding: const EdgeInsets.symmetric(horizontal: 24),
                              child: Column(
                                children: [
                                  ...mainDataList.reversed.map((e) => Recents(
                                      onClear: () {
                                        localList.remove('searchList');
                                        mainDataList.removeWhere((item) => item == e);
                                        localList.write('searchList', mainDataList);
                                        setState(() {});
                                      },
                                      onTap: () async {
                                        searchController.text = e;
                                        searchController.value = TextEditingValue(
                                          text: e,
                                          selection:
                                              TextSelection.collapsed(offset: searchController.text.length),
                                        );
                                        isRecentSearch = false;
                                        searchContent(context);
                                        setState(() {});
                                      },
                                      text: e)),
                                ],
                              ))
                          : blogList.isNotEmpty && isFound
                              ? isLoading == true
                                  ? const SizedBox()
                                  : Column(
                                      children: [
                                        ...blogList.asMap().entries.map((e) => ListWrapper(
                                            onTap: () {
                                              Navigator.pushNamed(context, '/BlogWrap', arguments: [
                                                e.key,
                                                true,
                                                pageController,
                                                e.value.id,
                                                null,
                                                true
                                              ]);
                                            },
                                            key: ValueKey(e.key),
                                            onChanged: (value) {
                                              e.value.isBookmark = value;
                                              provider.setBookmark(blog: e.value);
                                              setState(() {});
                                            },
                                            isSearch: true,
                                            e: e.value,
                                            index: e.key)),
                                      ],
                                    )
                              : isRecentSearch == true && searchController.text == ''
                                  ? const SizedBox()
                                  : isLoading == true
                                      ? const SizedBox()
                                      : Container(
                                          height: size(context).height / 2,
                                          padding: const EdgeInsets.symmetric(horizontal: 24),
                                          child: Column(
                                            mainAxisAlignment: MainAxisAlignment.center,
                                            crossAxisAlignment: CrossAxisAlignment.center,
                                            children: [
                                              Image.asset('assets/images/confuse.png',
                                                  color: dark(context) ? Colors.white : Colors.black,
                                                  width: 100,
                                                  height: 100),
                                              const SizedBox(height: 12),
                                              Text(
                                                allMessages.value.noResultsFoundMatchingWithYourKeyword ??
                                                    "No result found",
                                                textAlign: TextAlign.center,
                                                style: const TextStyle(
                                                    fontSize: 16,
                                                    fontFamily: 'Roboto',
                                                    fontWeight: FontWeight.w500),
                                              )
                                            ],
                                          ),
                                        ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void searchContent(BuildContext context) {
    searchFocusNode.unfocus();

    if (searchListShor) {
      isRecentSearch = false;
      getSearchedBlog();
      if (!mainDataList.contains(searchController.text)) {
        if (mainDataList.length >= 4) {
          localList.remove('searchList');
          mainDataList.removeAt(mainDataList.length - 1);
          addLocalStore();
          localList.write('searchList', mainDataList);
        } else {
          localList.remove('searchList');
          addLocalStore();
          localList.write('searchList', mainDataList);
        }
      } else {
        localList.remove('searchList');
        mainDataList.removeWhere((item) => item == searchController.text);
        addLocalStore();

        localList.write('searchList', mainDataList);
      }
      searchListShor = false;
    } else {
      searchListShor = true;
      searchController.text = '';
    }
    setState(() {
      searchListShor = false;
    });
  }

  void addLocalStore() {
    if (searchController.text.isNotEmpty) {
      mainDataList.add(searchController.text);
    }
  }

  Future getSearchedBlog() async {
    isLoading = true;
    setState(() {});
    final https = http.Client();
    try {
      if (searchController.text != '' && searchListShor == true) {
        final msg = jsonEncode({
          "keyword": searchController.text,
        });
        final String url = '${Urls.baseUrl}blog-search';

        final response = await https.post(
          Uri.parse(url),
          headers: currentUser.value.id != null
              ? {
                  HttpHeaders.contentTypeHeader: 'application/json',
                  "api-token": currentUser.value.apiToken ?? '',
                  "language-code": languageCode.value.language ?? '',
                }
              : {
                  HttpHeaders.contentTypeHeader: 'application/json',
                  "language-code": languageCode.value.language ?? '',
                },
          body: msg,
          //encoding: Encoding.getByName('utf-8')
        );
        Map<String, dynamic> data = json.decode(response.body);
        debugPrint(data.toString());
        setState(() {
          if (data['success'] == true) {
            isFound = true;
          } else {
            isFound = false;
          }

          final list = DataModel.fromJson(data, isSearch: true);
          isRecentSearch = false;
          blogList = list.blogs;
          blogListHolder2.clearList();
          blogListHolder2.setList(list);
          blogListHolder2.setBlogType(BlogType.search);
          isLoading = false;
        });
      } else {
        isRecentSearch = true;
        isLoading = false;
        setState(() {});
      }
    } finally {
      // Ensure to close the HTTP client when done
      https.close();
    }
  }
}

class SoundWidget extends StatefulWidget {
  const SoundWidget({
    super.key,
    this.words,
    required this.onTextChanged,
  });

  final String? words;
  final ValueChanged onTextChanged;

  @override
  State<SoundWidget> createState() => _SoundWidgetState();
}

class _SoundWidgetState extends State<SoundWidget> {
  String? displayText;

  @override
  void initState() {
    super.initState();
    displayText = widget.words;
  }

  void updateText(String newText) {
    setState(() {
      displayText = newText;
    });
    widget.onTextChanged(newText);
  }

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Container(
        width: size(context).width,
        height: size(context).height / 1.5,
        alignment: Alignment.center,
        margin: const EdgeInsets.all(15),
        padding: const EdgeInsets.all(24),
        child: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              AnimationFadeSlide(
                  repeat: true,
                  dx: 0,
                  dy: 0,
                  child: CircleAvatar(
                    radius: 26,
                    child: CircleAvatar(
                      backgroundColor: Theme.of(context).primaryColor,
                      child: const Icon(Icons.mic, color: Colors.white),
                    ),
                  )),
              const SizedBox(height: 20),
              Text(
                  '${allSettings.value.appName} ${allMessages.value.assistantIsListening ?? "Assitant is Listening..."}',
                  style: Theme.of(context).textTheme.bodyLarge),
              Container(
                height: 100,
                alignment: Alignment.center,
                child: StatefulBuilder(builder: (context, setState) {
                  return Text(
                    allMessages.value.speakYourKeyword ??
                        widget.words ??
                        "Please speak your keyword to search...",
                    style: Theme.of(context).textTheme.bodyMedium!.copyWith(color: Colors.grey),
                  );
                }),
              ),
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  TextButton(
                      style: TextButton.styleFrom(
                          backgroundColor: Theme.of(context).primaryColor.customOpacity(0.3)),
                      onPressed: () {
                        widget.onTextChanged('speak');
                      },
                      child: Text(
                        allMessages.value.speakAgain ?? 'Speak Again',
                        style: Theme.of(context).textTheme.bodyMedium,
                      )),
                  const SizedBox(width: 12),
                  TextButton(
                      onPressed: () {
                        widget.onTextChanged('cancel');
                      },
                      child: Text(
                        allMessages.value.cancelSearchSpeak ?? 'Cancel',
                        style: Theme.of(context).textTheme.bodyMedium!.copyWith(color: Colors.grey),
                      ))
                ],
              )
            ],
          ),
        ),
      ),
    );
  }
}

class Recents extends StatelessWidget {
  const Recents({
    super.key,
    required this.onClear,
    required this.onTap,
    this.text,
  });

  final String? text;
  final VoidCallback onTap, onClear;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        color: Colors.transparent,
        child: Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
          Expanded(
            child: Row(
              children: [
                const Icon(Icons.refresh_rounded, size: 16),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(text ?? '',
                      style:
                          const TextStyle(fontFamily: 'Roboto', fontSize: 15, fontWeight: FontWeight.w500)),
                ),
              ],
            ),
          ),
          IconButton(onPressed: onClear, icon: const Icon(Icons.close_rounded, size: 16))
        ]),
      ),
    );
  }
}
