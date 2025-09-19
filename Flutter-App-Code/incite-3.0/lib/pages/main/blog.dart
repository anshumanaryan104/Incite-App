import 'dart:developer';
import 'dart:io';

import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_tts/flutter_tts.dart';
import 'package:html/parser.dart';
import 'package:incite/api_controller/news_repo.dart';
import 'package:incite/pages/auth/signup.dart';
import 'package:incite/pages/main/web_view.dart';
import 'package:incite/pages/main/widgets/caurosal.dart';
import 'package:incite/pages/main/widgets/fullscreen.dart';
import 'package:incite/pages/main/widgets/poll.dart';
import 'package:incite/pages/main/widgets/share.dart';
import 'package:incite/urls/url.dart';
import 'package:incite/utils/app_theme.dart';
import 'package:incite/utils/color_util.dart';
import 'package:incite/utils/image_util.dart';
import 'package:incite/utils/nav_util.dart';
import 'package:incite/utils/rgbo_to_hex.dart';
import 'package:incite/utils/screen_util.dart';
import 'package:incite/widgets/incite_video_player.dart';
import 'package:incite/widgets/loader.dart';
import 'package:incite/widgets/shimmer.dart';
import 'package:incite/widgets/short_video_controller.dart';
import 'package:path_provider/path_provider.dart';
import 'package:provider/provider.dart';
import 'package:visibility_detector/visibility_detector.dart';
import '../../api_controller/app_provider.dart';
import '../../api_controller/user_controller.dart';
import '../../model/blog.dart';
import 'package:audioplayers/audioplayers.dart';
import '../../utils/theme_util.dart';
import '../../utils/time_util.dart';
import '../../utils/tts.dart';
import '../../widgets/banner_ads.dart';
import '../../widgets/custom_toast.dart';
import '../../widgets/tap.dart';
import 'home.dart';
import 'package:just_audio/just_audio.dart' as just;
import 'widgets/text.dart';

// GlobalKey previewContainer2 = GlobalKey();

GlobalKey scaffKey = GlobalKey<ScaffoldState>();

enum TtsState { playing, stopped, paused, continued }

enum BlogOptionType { share, bookmark }

class BlogPage extends StatefulWidget {
  final bool isVoting, isSingle, isBackAllowed;
  final Blog? model;
  final Category? category;
  final BlogOptionType? type;
  final int index, currIndex;
  final VoidCallback? onTap;
  final bool initial;
  final ValueChanged? onChanged;
  final List<GlobalKey>? tutorialkeysList;

  const BlogPage(
      {super.key,
      this.model,
      this.type,
      this.onChanged,
      this.isBackAllowed = false,
      this.index = 0,
      this.isSingle = false,
      this.initial = false,
      this.category,
      this.onTap,
      this.tutorialkeysList,
      this.isVoting = false,
      required this.currIndex});

  @override
  State<BlogPage> createState() => _BlogPageState();
}

class _BlogPageState extends State<BlogPage> with WidgetsBindingObserver {
  String? vote;

  bool isPlayerReady = false;

  GlobalKey previewContainer = GlobalKey();

  bool isExpand = true;
  bool isShare = false;
  final audioPlayer = AudioPlayer();
  just.AudioPlayer audioPlay = just.AudioPlayer();
  var previewContainer2 = GlobalKey<ScaffoldState>();
  AppProvider? provider;
  String startTime = '';
  late Map<String, dynamic> ttsData;
  // ignore: prefer_typing_uninitialized_variables
  var ttsState;

  FlutterTts flutterTts = FlutterTts();
  late DateTime blogStartTime;
  bool isVolume = false;
  Uint8List? audioLoad;

  bool isLeftSwipe = false;

  var admobFreq =
      int.parse(allSettings.value.enableAds == '0' ? '0' : allSettings.value.admobFrequency.toString());
  var fbadsFreq =
      int.parse(allSettings.value.enableFbAds == '0' ? '0' : allSettings.value.fbAdsFrequency.toString());
  var unityAdsFreq = int.parse(
      allSettings.value.enableUnityAds == '0' ? '0' : allSettings.value.unityAdsFrequency.toString());

  late Blog blog;

  GlobalKey<ScaffoldState> drawerKey = GlobalKey<ScaffoldState>();

  //  CachedVideoPlayerPlusController? videocontroller;

  @override
  void initState() {
    super.initState();

    blog = widget.model as Blog;
    WidgetsBinding.instance.addObserver(this);
    blogStartTime = DateTime.now();
    initTTS();
    ttsData = {"id": widget.model!.id, "start_time": "", "end_time": ""};

    WidgetsBinding.instance.addPostFrameCallback((timeStamp) async {
      provider = Provider.of<AppProvider>(context, listen: false);

      if (blog.videoUrl != null && blog.videoUrl!.isNotEmpty) {
        if (Platform.isAndroid) {
          ScreenTimeout().enableScreenAwake();
        }
      }
      if (blogPolls != null && blogPolls!.blogs.contains(widget.model)) {
        var blogIndex = blogPolls!.blogs.indexOf(widget.model as Blog);
        blog = blogPolls!.blogs[blogIndex];
      }
      if (Platform.isIOS) {
        await flutterTts.setIosAudioCategory(
            IosTextToSpeechAudioCategory.ambient,
            [
              IosTextToSpeechAudioCategoryOptions.allowBluetooth,
              IosTextToSpeechAudioCategoryOptions.allowBluetoothA2DP,
              IosTextToSpeechAudioCategoryOptions.mixWithOthers
            ],
            IosTextToSpeechAudioMode.voicePrompt);
        await flutterTts.awaitSynthCompletion(true);
      }

      if (widget.type != null && widget.type == BlogOptionType.share) {
        Future.delayed(const Duration(milliseconds: 2000), () async {
          await captureScreenshot(previewContainer, isPost: true).then((value) async {
            isShare = true;
            setState(() {});
            if (value != null) {
              final data2 = await convertToXFile(value);
              Future.delayed(const Duration(milliseconds: 100));
              shareImage(data2, "${Urls.baseServer}share-blog?blog_id=${blog.id}");
              provider!.addShareData(blog.id!.toInt());
            }
            isShare = false;
            setState(() {});
          });
        });
      } else if (widget.type != null && widget.type == BlogOptionType.bookmark) {
        if (currentUser.value.id != null) {
          if (!provider!.permanentIds.contains(blog.id)) {
            showCustomToast(context, allMessages.value.bookmarkSave ?? 'Bookmark Saved');
            provider!.addBookmarkData(blog.id!.toInt());
          } else {
            showCustomToast(context, allMessages.value.bookmarkRemove ?? 'Bookmark Removed');
            provider!.removeBookmarkData(blog.id!.toInt());
          }
          provider!.setBookmark(blog: blog);
        } else {
          Navigator.pushNamed(context, '/LoginPage');
        }
      }

      setState(() {});
    });
  }

  Future<Uint8List?> speech(String text) async {
    final response = await generateSpeech(text, widget.model!.accentCode.toString(), 'en-GB-Neural2-A');
    setState(() {
      audioLoad = response;
      widget.model!.audioData = audioLoad;
    });
    return audioLoad;
  }

  Future<void> setSpeechRate(double rate) async {
    await flutterTts.setSpeechRate(rate); // Set the speech rate using the FlutterTts plugin
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    switch (state) {
      case AppLifecycleState.resumed:
        // App is resumed and visible
        break;
      case AppLifecycleState.inactive:
        // App is inactive and not receiving user input
        break;
      case AppLifecycleState.paused:
        if (allSettings.value.isVoiceEnabled == true) {
          stops();
        }
        break;
      case AppLifecycleState.detached:
        // App is detached from any host and all resources have been released
        break;
      case AppLifecycleState.hidden:
        break;
    }
    super.didChangeAppLifecycleState(state);
  }

  Future playLocal(Uint8List audioData) async {
    await Future.delayed(const Duration(milliseconds: 100));
    if (Platform.isAndroid) {
      Source source = BytesSource(audioData);
      audioPlayer.setPlaybackRate(0.833);
      await audioPlayer.play(
        source,
        volume: 1.0,
        mode: PlayerMode.mediaPlayer,
      );
    } else {
      playLocalAudio(audioData);
    }
  }

  Future<void> playLocalAudio(Uint8List tts) async {
    final appDocDir = await getApplicationDocumentsDirectory();
    final filePath = "${appDocDir.path}/tts_audio.wav";
    final file = File.fromUri(Uri.parse(filePath));
    await file.writeAsBytes(tts);
    just.AudioSource source = just.AudioSource.file(file.path);
    await audioPlay.setAudioSource(source);
    await audioPlay.setSpeed(0.3);
    await Future.delayed(const Duration(milliseconds: 100));
    if (file.existsSync()) {
      await audioPlay.play();
    } else {}
  }

  UrlSource urlSourceFromBytes(Uint8List bytes, {String mimeType = "audio/wav"}) {
    return UrlSource(Uri.dataFromBytes(bytes, mimeType: mimeType).toString());
  }

  void stops() {
    if (allSettings.value.isVoiceEnabled == true) {
      isVolume = false;

      if (Platform.isIOS) {
        audioPlay.stop();
        stop();
      } else {
        stop();
        audioPlayer.stop();
      }
      setState(() {});
    }
  }

  Future<void> init(String text) async {
    bool isLanguageFound = false;
    var filterText = text.replaceAll(RegExp(r'[^\w\s,.]'), '');
    flutterTts.getLanguages.then((value) async {
      Iterable it = value;

      for (var element in it) {
        if (element.toString().contains(widget.model!.accentCode.toString())) {
          flutterTts.setLanguage(element);
          setSpeechRate(0.33);
          _playVoice(filterText);
          isLanguageFound = true;
        }
      }
    });

    if (!isLanguageFound) {
      _playVoice(filterText);
    }
  }

  void audioListener() {
    audioPlayer.onPlayerStateChanged.listen(
      (it) {
        switch (it) {
          case PlayerState.completed:
            setState(() {
              isVolume = false;
              var endTime = DateTime.now().toIso8601String();
              ttsData = {"id": widget.model!.id, "start_time": startTime, "end_time": endTime};
              provider!.addTtsData(ttsData['id'], ttsData['start_time'], ttsData['end_time']);
            });
            break;
          default:
            break;
        }
      },
    );
  }

  @override
  void didChangeDependencies() {
    WidgetsBinding.instance.addPostFrameCallback((timeStamp) {
      if (widget.index == 0) {
        provider!.addviewData(blog.id ?? 0);
      }
      if (allSettings.value.isVoiceEnabled == true) {
        if (ttsState == TtsState.stopped) {
          setState(() {
            isVolume = false;
          });
        }
      }
    });
    super.didChangeDependencies();
  }

  _playVoice(text) async {
    setState(() {
      ttsState == TtsState.playing;
      flutterTts.speak(text).then((value) {
        initTTS();
      });

      isVolume = true;
    });
  }

  Future<void> initTTS() async {
    flutterTts = FlutterTts();

    flutterTts.setStartHandler(() {
      setState(() {
        ttsState = TtsState.playing;
      });
    });

    flutterTts.setCompletionHandler(() {
      stop();
      var endTime = DateTime.now().toIso8601String();
      ttsData = {"id": widget.model!.id, "start_time": startTime, "end_time": endTime};
      provider!.addTtsData(ttsData['id'], ttsData['start_time'], ttsData['end_time']);
      setState(() {});
    });

    flutterTts.setPauseHandler(() {
      setState(() {
        ttsState = TtsState.paused;
      });
    });

    flutterTts.setContinueHandler(() {
      setState(() {
        ttsState = TtsState.continued;
      });
    });

// Replace all occurrences of the comma with an empty string
  }

  Future stop() async {
    ttsState == TtsState.stopped;
    flutterTts.stop();
    isVolume = false;
    if (mounted) {
      setState(() {});
    }
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    if (allSettings.value.isVoiceEnabled == true) {
      audioPlayer.stop();
      flutterTts.stop();

      if (Platform.isIOS) {
        audioPlay.stop();
      }
    }
    super.dispose();
  }

  String endTime = '';

  bool isVideoDifferentPlay = false;

  @override
  Widget build(BuildContext context) {
    var provider2 = Provider.of<AppProvider>(context, listen: false);
    SystemChrome.setSystemUIOverlayStyle(
        SystemUiOverlayStyle(statusBarColor: dark(context) ? ColorUtil.blogBackColor : Colors.white));

    var timeFormat2 =
        blog.scheduleDate == null ? '' : timeFormat(DateTime.tryParse(blog.scheduleDate!.toIso8601String()));
    var orientation = MediaQuery.of(context).orientation;

    if (orientation == Orientation.landscape) {
      return PlayVideoUsingExtraction(context);
    }

    // log(widget.index.toString());
    log(unityAdsFreq.toString());
    return SizedBox(
      height: size(context).height,
      width: MediaQuery.of(context).size.width,
      child: CustomLoader(
          isLoading: isShare,
          child: Scaffold(
              key: drawerKey,
              drawerEdgeDragWidth: MediaQuery.of(context).size.width / 1.5,
              onEndDrawerChanged: (isOpened) {
                widget.onChanged!(isOpened);
                isLeftSwipe = isOpened;
                setState(() {});
              },
              endDrawer: blog.sourceLink != ''
                  ? CustomWebView(
                      url: blog.sourceLink.toString(),
                      onTap: () {
                        drawerKey.currentState!.closeEndDrawer();
                      },
                    )
                  : null,
              body: blog.title == null && blog.description == null
                  ? Padding(
                      padding: const EdgeInsets.all(16.0),
                      child: Column(
                        children: [
                          ShimmerLoader(
                            width: size(context).width,
                            height: 250,
                            margin: const EdgeInsets.only(top: 30),
                          ),
                          const SizedBox(height: 12),
                          Row(
                            children: [
                              const ShimmerLoader(
                                width: 100,
                                height: 20,
                                borderRadius: 16,
                              ),
                              const Spacer(),
                              ...List.generate(
                                  3,
                                  (index) => const ShimmerLoader(
                                        width: 36,
                                        height: 36,
                                        borderRadius: 100,
                                      ))
                            ],
                          ),
                          const SizedBox(height: 12),
                          ShimmerLoader(
                            width: size(context).width,
                            height: size(context).height / 2.15,
                            borderRadius: 16,
                          ),
                          const SizedBox(height: 12),
                          Row(
                            children: [
                              ...List.generate(
                                  2,
                                  (index) => const ShimmerLoader(
                                        width: 120,
                                        height: 20,
                                        borderRadius: 10,
                                      ))
                            ],
                          )
                        ],
                      ),
                    )
                  : SafeArea(
                      child: Stack(
                        fit: StackFit.expand,
                        children: [
                          Container(
                            color: Theme.of(context).scaffoldBackgroundColor,
                            width: size(context).width,
                            height: size(context).height,
                            child: RepaintBoundary(
                              key: previewContainer2,
                              child: Container(
                                color: Theme.of(context).scaffoldBackgroundColor,
                                child: Column(
                                  children: [
                                    Expanded(
                                      child: RepaintBoundary(
                                        key: previewContainer,
                                        child: Container(
                                          color: Theme.of(context).scaffoldBackgroundColor,
                                          padding: EdgeInsets.zero,
                                          child: Column(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              Column(
                                                children: [
                                                  widget.model!.videoUrl!.isNotEmpty
                                                      ? isVideoDifferentPlay == true
                                                          ? PlayVideoUsingExtraction(context)
                                                          : PlayAnyVideoPlayer(
                                                              model: blog,
                                                              isCurrentlyOpened:
                                                                  widget.currIndex == widget.index,
                                                              isPlayCenter: true,
                                                              onChanged: (val) {
                                                                isVideoDifferentPlay = true;
                                                                setState(() {});
                                                              },
                                                            )
                                                      : blog.images != null && blog.images!.length > 1
                                                          ? CaurosalSlider(
                                                              model: blog,
                                                            )
                                                          : GestureDetector(
                                                              onTap: () {
                                                                Navigator.push(
                                                                    context,
                                                                    PagingTransform(
                                                                        widget: FullScreen(
                                                                          image: blog.images != null &&
                                                                                  blog.images!.isNotEmpty
                                                                              ? blog.images![0].toString()
                                                                              : '',
                                                                          index: widget.index,
                                                                          title: blog.title.toString(),
                                                                        ),
                                                                        slideUp: true));
                                                              },
                                                              child: Hero(
                                                                tag: blog.images!.isEmpty
                                                                    ? '${widget.index}'
                                                                    : '${widget.index}${blog.images![0]}',
                                                                child: Stack(
                                                                  children: [
                                                                    Container(
                                                                      width: size(context).width,
                                                                      height: height10(context) * 25,
                                                                      decoration: BoxDecoration(
                                                                        color: Theme.of(context).cardColor,
                                                                        borderRadius:
                                                                            BorderRadius.circular(20),
                                                                      ),
                                                                      child: ClipRRect(
                                                                        borderRadius:
                                                                            BorderRadius.circular(20),
                                                                        child: blog.images!.isEmpty
                                                                            ? AppIcon(
                                                                                isHandlerImage: true,
                                                                                fit: BoxFit.cover,
                                                                              )
                                                                            : CachedNetworkImage(
                                                                                imageUrl: blog.images != null
                                                                                    ? blog.images![0]
                                                                                    : '',
                                                                                fit: BoxFit.cover,
                                                                                errorWidget:
                                                                                    (context, url, error) {
                                                                                  return const ShimmerLoader(
                                                                                    margin: EdgeInsets.zero,
                                                                                  );
                                                                                },
                                                                                placeholder: (context, url) {
                                                                                  return const ShimmerLoader(
                                                                                    margin: EdgeInsets.zero,
                                                                                  );
                                                                                },
                                                                              ),
                                                                      ),
                                                                    ),
                                                                    if (blog.videoUrl != '' &&
                                                                        widget.currIndex != widget.index)
                                                                      Positioned.fill(
                                                                          child: Container(
                                                                        color: Colors.black54,
                                                                        child: const Center(
                                                                          child: CircularProgressIndicator(),
                                                                        ),
                                                                      ))
                                                                  ],
                                                                ),
                                                              ),
                                                            ),
                                                  visibleAtScreenshot(orientation, context),
                                                ],
                                              ),
                                              orientation == Orientation.landscape
                                                  ? const SizedBox()
                                                  : Padding(
                                                      padding: const EdgeInsets.only(left: 16, right: 16),
                                                      child: TitleWidget(
                                                          key: Key('${blog.hashCode}'),
                                                          title: blog.title.toString()),
                                                    ),
                                              SizedBox(height: height10(context)),
                                              orientation == Orientation.landscape
                                                  ? const SizedBox()
                                                  : Expanded(
                                                      flex: 2,
                                                      child: Padding(
                                                        padding: const EdgeInsets.only(left: 16, right: 16),
                                                        child: Column(
                                                          crossAxisAlignment: CrossAxisAlignment.start,
                                                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                                          children: [
                                                            Expanded(
                                                              child: Description(
                                                                  optionLength: blog.question == null
                                                                      ? 0
                                                                      : blog.question!.options != null
                                                                          ? blog.question!.options!.length
                                                                          : 0,
                                                                  model: blog,
                                                                  isPoll: isExpand &&
                                                                      blog.question != null &&
                                                                      blog.isVotingEnable == 1),
                                                            ),
                                                            if (blog.question == null &&
                                                                blog.isVotingEnable == 0)
                                                              timeAndSourceWrap(context, timeFormat2),
                                                          ],
                                                        ),
                                                      ),
                                                    )
                                            ],
                                          ),
                                        ),
                                      ),
                                    ),
                                    orientation == Orientation.landscape
                                        ? const SizedBox()
                                        : blog.question != null && blog.isVotingEnable == 1
                                            ? BlogPoll(
                                                pollKey: previewContainer2,
                                                onChanged: (value) {
                                                  isExpand = value;
                                                  setState(() {});
                                                },
                                                model: blog,
                                                isBlogOpened: true)
                                            : const SizedBox(),
                                    // --------------------------------

                                    if (blog.question != null && blog.isVotingEnable == 1)
                                      timeAndSourceWrap(context, timeFormat2, isPoll: true),

                                    // -------------- FB Banner Ads -----------------------------

                                    orientation == Orientation.landscape
                                        ? const SizedBox()
                                        : allSettings.value.enableFbAds == '1' &&
                                                widget.index >=
                                                    int.parse(allSettings.value.fbAdsFrequency.toString()) &&
                                                widget.index % (admobFreq + fbadsFreq) == 0
                                            ? Platform.isIOS && allSettings.value.fbAdsPlacementIdIos != null
                                                ? facebookads(context)
                                                : Platform.isAndroid &&
                                                        allSettings.value.fbAdsPlacementIdAndroid != null
                                                    ? facebookads(context)
                                                    : const SizedBox()
                                            : const SizedBox(),

                                    // -------------- Admob Banner Ads -----------------------------
                                    orientation == Orientation.landscape
                                        ? const SizedBox()
                                        : allSettings.value.enableAds == '1' &&
                                                widget.index >=
                                                    int.parse(allSettings.value.admobFrequency.toString()) &&
                                                widget.index % admobFreq == 0
                                            ? Platform.isIOS && allSettings.value.admobBannerIdIos != null
                                                ? bannerAdMob(context)
                                                : Platform.isAndroid &&
                                                        allSettings.value.admobBannerIdAndroid != null
                                                    ? bannerAdMob(context)
                                                    : const SizedBox()
                                            : const SizedBox(),

                                    // -------------- Unity Banner Ads -----------------------------
                                    orientation == Orientation.landscape
                                        ? const SizedBox()
                                        : allSettings.value.enableUnityAds == '1' &&
                                                widget.index >=
                                                    int.parse(
                                                        allSettings.value.unityAdsFrequency.toString()) &&
                                                (widget.index + 1) %
                                                        (admobFreq + fbadsFreq + (unityAdsFreq + 1)) ==
                                                    0
                                            ? Platform.isIOS && allSettings.value.unityAdsBannerIdIos != null
                                                ? UnityInAppAd()
                                                : Platform.isAndroid &&
                                                        allSettings.value.unityAdsBannerIdAndroid != null
                                                    ? UnityInAppAd()
                                                    : const SizedBox()
                                            : const SizedBox(),
                                  ],
                                ),
                              ),
                            ),
                          ),
                          orientation == Orientation.landscape
                              ? const SizedBox()
                              : Positioned(
                                  top: blog.videoUrl != ''
                                      ? height10(context) * 25.5
                                      : height10(context) * 26.35,
                                  child: Container(
                                    width: size(context).width,
                                    alignment: Alignment.center,
                                    color: Theme.of(context).scaffoldBackgroundColor,
                                    padding:
                                        EdgeInsets.only(bottom: height10(context) - 5, left: 16, right: 16),
                                    child: Row(
                                      crossAxisAlignment: CrossAxisAlignment.center,
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Row(
                                          children: blog.blogSubCategory != null &&
                                                  blog.blogSubCategory!.isNotEmpty
                                              ? [
                                                  CategoryWrap(
                                                      color: blog.blogSubCategory!.first.color != null
                                                          ? hexToRgb(
                                                              blog.blogSubCategory!.first.color.toString())
                                                          : Colors.black,
                                                      colored: blog.categoryColor.toString(),
                                                      name: blog.blogSubCategory!.first.name.toString()),
                                                ]
                                              : [
                                                  CategoryWrap(
                                                      color: blog.categoryColor != null
                                                          ? hexToRgb(blog.categoryColor.toString())
                                                          : Colors.black,
                                                      colored: blog.categoryColor.toString(),
                                                      name: blog.categoryName.toString()),
                                                ],
                                        ),
                                        VisibilityDetector(
                                          key: const Key("s"),
                                          onVisibilityChanged: (visibilityInfo) async {
                                            var visiblePercentage = visibilityInfo.visibleFraction * 100.0;

                                            if (visiblePercentage != 100.0) {
                                              if (isVolume) {
                                                stop();
                                                if (allSettings.value.googleApikey != null &&
                                                    allSettings.value.isVoiceEnabled == true) {
                                                  stops();
                                                }
                                                endTime = DateTime.now().toIso8601String();

                                                ttsData = {
                                                  "id": blog.id,
                                                  "start_time": startTime,
                                                  "end_time": endTime
                                                };

                                                provider!.addTtsData(ttsData['id'], ttsData['start_time'],
                                                    ttsData['end_time']);
                                              }

                                              if (mounted) {
                                                isVolume = false;
                                                setState(() {});
                                              }
                                            }
                                          },
                                          child: PostFeatureWrap(
                                              isVolume: isVolume,
                                              onVoice: () async {
                                                if (allSettings.value.googleApikey != null &&
                                                    allSettings.value.isVoiceEnabled == true) {
                                                  if (isVolume == false) {
                                                    isVolume = true;
                                                    if (blog.audioData == null) {
                                                      await speech(
                                                              parse("${blog.title}....${blog.description}")
                                                                  .body!
                                                                  .text)
                                                          .then((value) async {
                                                        if (value != null) {
                                                          playLocal(value);
                                                        }
                                                      });
                                                    } else {
                                                      playLocal(audioLoad ?? blog.audioData as Uint8List);
                                                    }
                                                  } else {
                                                    isVolume = true;
                                                    stops();
                                                  }
                                                  setState(() {});
                                                } else {
                                                  if (isVolume == false) {
                                                    init(parse("${blog.title}....${blog.description}")
                                                        .body!
                                                        .text);
                                                    isVolume = true;
                                                    //if (startTime == '') {
                                                    startTime = DateTime.now().toIso8601String();
                                                    ttsData = {
                                                      "id": blog.id,
                                                      "start_time": startTime,
                                                      "end_time": endTime
                                                    };
                                                    setState(() {});
                                                  } else {
                                                    stop();
                                                    endTime = DateTime.now().toIso8601String();
                                                    ttsData = {
                                                      "id": blog.id,
                                                      "start_time": startTime,
                                                      "end_time": endTime
                                                    };
                                                    provider!.addTtsData(ttsData['id'], ttsData['start_time'],
                                                        ttsData['end_time']);
                                                    isVolume = false;
                                                  }
                                                }
                                                setState(() {});
                                              },
                                              provider: provider2,
                                              model: blog,
                                              isBookmarkContains: provider != null
                                                  ? provider!.permanentIds.contains(blog.id)
                                                  : false,
                                              onBookmark: currentUser.value.id == null
                                                  ? () {
                                                      Navigator.pushNamed(context, '/LoginPage');
                                                    }
                                                  : () {
                                                      if (!provider!.permanentIds.contains(blog.id)) {
                                                        showCustomToast(
                                                            context,
                                                            allMessages.value.bookmarkSave ??
                                                                'Bookmark Saved');
                                                        provider!.addBookmarkData(blog.id!.toInt());
                                                      } else {
                                                        showCustomToast(
                                                            context,
                                                            allMessages.value.bookmarkRemove ??
                                                                'Bookmark Removed');
                                                        provider!.removeBookmarkData(blog.id!.toInt());
                                                      }
                                                      provider!.setBookmark(blog: blog);
                                                      setState(() {});
                                                    },
                                              onShare: () async {
                                                // await createDynamicLink(blog ?? Blog())
                                                isShare = true;
                                                setState(() {});
                                                Future.delayed(const Duration(milliseconds: 100));
                                                await captureScreenshot(previewContainer, isPost: true)
                                                    .then((value) async {
                                                  Future.delayed(const Duration(milliseconds: 10));
                                                  final data2 = await convertToXFile(value!);
                                                  Future.delayed(const Duration(milliseconds: 10));
                                                  shareImage(
                                                      data2, "${Urls.baseServer}blog-share?id=${blog.id}");
                                                  provider!.addShareData(blog.id!.toInt());
                                                  isShare = false;
                                                  setState(() {});
                                                });
                                              }),
                                        )
                                      ],
                                    ),
                                  ),
                                ),
                        ],
                      ),
                    ))),
    );
  }

  SizedBox PlayVideoUsingExtraction(BuildContext context) {
    return SizedBox(
      height: size(context).height * 0.3,
      child: ShortPlayAnyVideo(
          model: widget.model,
          currIndex: widget.currIndex,
          index: widget.index,
          onChangedOrientation: (value) {
            if (value == true) {
              SystemChrome.setPreferredOrientations([
                DeviceOrientation.landscapeRight,
                DeviceOrientation.landscapeLeft,
              ]);
            } else {
              SystemChrome.setPreferredOrientations([
                DeviceOrientation.portraitUp,
                DeviceOrientation.portraitDown,
              ]);
            }
          },
          isAutoPlay: appThemeModel.value.isAutoPlay.value),
    );
  }

  Widget visibleAtScreenshot(Orientation orientation, BuildContext context) {
    return orientation == Orientation.landscape
        ? const SizedBox()
        : Container(
            padding: EdgeInsets.only(left: 16, right: 16, top: height10(context), bottom: height10(context)),
            child: Row(
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(12),
                  child: Image.asset(
                    'assets/images/playstore.webp',
                    width: 70,
                    height: 40,
                  ),
                ),
                SizedBox(width: 8),
                if (allSettings.value.appStoreUrl != null && allSettings.value.appStoreUrl!.contains('https'))
                  ClipRRect(
                      borderRadius: BorderRadius.circular(12),
                      child: Image.asset(
                        'assets/images/appstore.webp',
                        width: 70,
                        height: 40,
                      )),
                Spacer(),
                const RectangleAppIcon(
                  width: 80,
                  height: 25,
                )
              ],
            ));
  }

  Container timeAndSourceWrap(BuildContext context, String timeFormat2, {bool isPoll = false}) {
    return Container(
      alignment: Alignment.centerLeft,
      padding: EdgeInsets.only(left: isPoll ? 16 : 0, top: 8, bottom: isPoll ? 12 : 10),
      child: RichText(
        text: TextSpan(
          children: [
            blog.sourceLink == ''
                ? const WidgetSpan(child: SizedBox())
                : WidgetSpan(
                    alignment: PlaceholderAlignment.middle,
                    child: TapInk(
                      onTap: () {
                        Navigator.push(
                            context,
                            CupertinoPageRoute(
                                builder: (context) => CustomWebView(
                                    // onTap: () {
                                    //     drawerKey.currentState!.closeEndDrawer();
                                    //   },
                                    url: blog.sourceLink.toString())));
                      },
                      child: Container(
                        decoration: BoxDecoration(
                            border: Border(
                                bottom: BorderSide(
                                    width: 1,
                                    color: isBlack(Theme.of(context).primaryColor) && dark(context)
                                        ? ColorUtil.textWhite.withAlpha(166)
                                        : Theme.of(context).primaryColor))),
                        child: Text('${blog.sourceName}',
                            style: TextStyle(
                              fontFamily: 'Roboto',
                              fontSize: 14,
                              fontWeight: FontWeight.w300,
                              color: isBlack(Theme.of(context).primaryColor) && dark(context)
                                  ? Colors.white
                                  : Theme.of(context).primaryColor,
                            )),
                      ),
                    )),
            TextSpan(
              text: blog.sourceLink == '' ? timeFormat2 : " : $timeFormat2",
              style: TextStyle(
                fontFamily: 'Roboto',
                fontSize: 14,
                fontWeight: FontWeight.w300,
                color: dark(context) ? ColorUtil.textWhite.withAlpha(166) : ColorUtil.textgrey,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Container bannerAdMob(BuildContext context) {
    return Container(
      key: ValueKey("GoogleAds${widget.index}"),
      padding: const EdgeInsets.symmetric(horizontal: 20),
      width: size(context).width,
      height: height10(context) * 5,
      alignment: Alignment.center,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(10),
      ),
      child: BannerAds(
        adUnitId: Platform.isIOS
            ? allSettings.value.admobBannerIdIos ?? ''
            : allSettings.value.admobBannerIdAndroid ?? '',
      ),
    );
  }

  Container facebookads(BuildContext context) {
    return Container(
        key: ValueKey('FbAds${widget.index}'),
        padding: const EdgeInsets.symmetric(horizontal: 20),
        width: size(context).width,
        height: height10(context) * 5,
        alignment: Alignment.center,
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(10),
        ),
        child: FacebookAd(
          adUnitId: Platform.isIOS
              ? allSettings.value.fbAdsPlacementIdIos ?? ''
              : allSettings.value.fbAdsPlacementIdAndroid ?? '',
        ));
  }
}
