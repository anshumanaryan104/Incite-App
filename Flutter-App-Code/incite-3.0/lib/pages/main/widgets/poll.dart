import 'dart:convert';
import 'dart:developer';
import 'dart:io';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_svg/svg.dart';
import 'package:incite/api_controller/app_provider.dart';
import 'package:incite/api_controller/blog_controller.dart';
import 'package:incite/api_controller/news_repo.dart';
import 'package:incite/pages/auth/signup.dart';
import 'package:incite/pages/main/widgets/share.dart';
import 'package:incite/utils/app_theme.dart';
import 'package:incite/utils/image_util.dart';
import 'package:incite/widgets/custom_toast.dart';
import 'package:incite/widgets/shimmer.dart';
import 'package:incite/widgets/tap.dart';
import 'package:provider/provider.dart';
import '../../../api_controller/user_controller.dart';
import '../../../model/blog.dart';
import 'package:http/http.dart' as http;
import '../../../urls/url.dart';
import '../../../utils/color_util.dart';
import '../../../utils/theme_util.dart';

class BlogPoll extends StatefulWidget {
  const BlogPoll(
      {super.key,
      this.model,
      this.isStaticHeight = false,
      this.isBlogOpened = false,
      required this.pollKey,
      required this.onChanged});
  final Blog? model;
  final GlobalKey<State<StatefulWidget>> pollKey;
  final ValueChanged onChanged;
  final bool? isBlogOpened, isStaticHeight;

  @override
  State<BlogPoll> createState() => _BlogPollState();
}

class _BlogPollState extends State<BlogPoll> {
  int? vote;
  bool isLoading = false;
  bool isExpand = true;

  void _saveVoting(int option) async {
    try {
      final msg = jsonEncode({"option_id": option, 'blog_id': widget.model!.id});

      final String url = '${Urls.baseUrl}add-vote';
      final client = http.Client();
      final response = await client.post(
        Uri.parse(url),
        headers: {
          HttpHeaders.contentTypeHeader: 'application/json',
          'api-token': currentUser.value.apiToken.toString(),
        },
        body: msg,
      );

      debugPrint("_saveVoting response ${response.body}");
      Map data = json.decode(response.body);

      if (data['success'] == true) {
        widget.model!.isVote = option;
        widget.model!.question!.options = [];
        data['data']['options'].forEach((e) {
          widget.model!.question!.options!.add(PollOption.fromJSON(e));
        });
        if (blogPolls != null && blogPolls!.blogs.contains(widget.model)) {
          var blogIndex = blogPolls!.blogs.indexOf(widget.model as Blog);
          blogPolls!.blogs[blogIndex].isVote = option;
          blogPolls!.blogs[blogIndex].question!.options = [];
          data['data']['options'].forEach((e) {
            blogPolls!.blogs[blogIndex].question!.options!.add(PollOption.fromJSON(e));
          });
        }
      }
      isLoading = false;
      setState(() {});
    } on SocketException {
      isLoading = false;
      setState(() {});

      showCustomToast(context, allMessages.value.noInternetConnection ?? "");
    } catch (e) {
      isLoading = false;
      setState(() {});
      debugPrint(e.toString());
    }
  }

  @override
  Widget build(BuildContext context) {
    var provider = Provider.of<AppProvider>(context, listen: false);
    var textStyle = const TextStyle(fontFamily: 'Roboto', fontSize: 14, color: Colors.white);
    return SizedBox(
      height: widget.isStaticHeight == true ? 116 : null,
      width: size(context).width,
      child: Stack(
        fit: widget.isStaticHeight == true ? StackFit.expand : StackFit.loose,
        children: [
          SizedBox(
            width: size(context).width,
            height: widget.isStaticHeight == true ? 116 : null,
            // padding: const EdgeInsets.symmetric(horizontal: 0, vertical: 2),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                widget.isStaticHeight == true
                    ? Expanded(child: expandContractPoll(context, textStyle, provider))
                    : expandContractPoll(context, textStyle, provider),
              ],
            ),
          ),
          isLoading
              ? Positioned.fill(
                  child: AnimatedContainer(
                  duration: const Duration(milliseconds: 200),
                  curve: Curves.easeIn,
                  decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(20),
                      color: isLoading
                          ? dark(context)
                              ? Colors.grey.shade700.customOpacity(0.85)
                              : ColorUtil.whiteGrey.customOpacity(0.9)
                          : Colors.transparent),
                ))
              : const SizedBox(),
          isLoading
              ? const Positioned.fill(left: 24, right: 24, child: Center(child: CircularProgressIndicator()))
              : const SizedBox()
        ],
      ),
    );
  }

  GestureDetector expandContractPoll(BuildContext context, TextStyle textStyle, AppProvider provider) {
    return GestureDetector(
      onTap: widget.isStaticHeight == false
          ? () {
              isExpand = !isExpand;
              widget.onChanged(isExpand);
              setState(() {});
            }
          : null,
      child: AnimatedContainer(
        duration: const Duration(microseconds: 300),
        curve: Curves.easeInOut,
        // decoration: BoxDecoration(
        //     color: dark(context) ? ColorUtil.textblack : ColorUtil.whiteGrey,
        //     borderRadius: BorderRadius.circular(6)),
        padding: const EdgeInsets.symmetric(horizontal: 15),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Row(
              children: [
                Expanded(
                  child: Padding(
                    padding: const EdgeInsets.symmetric(vertical: 5, horizontal: 15),
                    child: Text(widget.model!.question != null ? widget.model!.question!.question ?? '' : "",
                        textAlign: TextAlign.center,
                        style: textStyle.copyWith(
                            fontWeight: FontWeight.w500,
                            color: dark(context) ? ColorUtil.white : ColorUtil.textblack)),
                  ),
                ),
                if (widget.isStaticHeight == false)
                  Padding(
                    padding: const EdgeInsets.only(right: 8.0),
                    child: RotatedBox(
                        quarterTurns: isExpand == false ? 1 : -1, child: const Icon(Icons.chevron_left)),
                  ),
                // if (isExpand) SizedBox(width: 12),
                isExpand && widget.model!.isVote != 0 && widget.isStaticHeight == false
                    ? TapInk(
                        pad: 4,
                        splash: Colors.transparent,
                        onTap: () async {
                          Future.delayed(const Duration(milliseconds: 100));
                          await captureScreenshot(widget.pollKey, isPost: true).then((value) async {
                            Future.delayed(const Duration(milliseconds: 10));
                            final data2 = await convertToXFile(value!);
                            Future.delayed(const Duration(milliseconds: 10));
                            shareImage(data2, "${Urls.baseUrl}share-blog?id=${widget.model!.id}");
                            provider.addPollShare(widget.model!.id!.toInt());
                          });
                          setState(() {});
                        },
                        child: SvgPicture.asset(
                          SvgImg.share,
                          colorFilter:
                              colorFilterMode(context, color: dark(context) ? Colors.white : Colors.black),
                        ),
                      )
                    : const SizedBox()
              ],
            ),
            SizedBox(height: 12),
            Wrap(
              key: const ValueKey('boat'),
              spacing: 6,
              children: [
                Table(
                  border: TableBorder.all(width: 0, color: Colors.transparent),
                  children: [
                    TableRow(
                      children: [
                        ...widget.model!.question!.options!.take(2).map((val) {
                          return pollOption(val, context);
                        })
                      ],
                    ),
                    if (widget.model!.question!.options!.length == 4)
                      TableRow(
                        children: [
                          ...widget.model!.question!.options!.skip(2).map((val) {
                            return pollOption(val, context);
                          })
                        ],
                      )
                  ],
                )
//  ClipRRect(
//                           borderRadius: BorderRadius.circular(8),
//                           child: Stack(
//                             children: [
//                               InkWell(
//                                 onTap: () {
//                                   if (currentUser.value.id != null) {
//                                     if (widget.model!.isVote != 0) {
//                                       //  showCustomToast(context, 'Vote already registered');
//                                     } else {
//                                       if (currentUser.value.id == null) {
//                                         Navigator.pushNamed(context, '/LoginPage');
//                                       } else {
//                                         isLoading = true;
//                                         setState(() {});
//                                         _saveVoting(widget.model!.question!.options![index].id!.toInt());
//                                       }
//                                     }
//                                   } else {
//                                     Navigator.pushNamed(context, '/LoginPage');
//                                   }
//                                 },
//                                 child: Container(
//                                   margin: const EdgeInsets.only(bottom: 4),
//                                   width: widget.isBlogOpened == true
//                                       ? size(context).width / 2.2
//                                       : size(context).width / 3.1,
//                                   // padding: const EdgeInsets.symmetric(vertical: 4),
//                                   decoration: BoxDecoration(
//                                       borderRadius: const BorderRadius.only(
//                                         topRight: Radius.circular(8),
//                                         bottomLeft: Radius.circular(8),
//                                         topLeft: Radius.circular(8),
//                                       ),
//                                       border: Border.all(
//                                           width: widget.model!.isVote ==
//                                                   widget.model!.question!.options![index].id
//                                               ? 1.25
//                                               : 1,
//                                           color: widget.model!.isVote ==
//                                                   widget.model!.question!.options![index].id
//                                               ? Theme.of(context).primaryColor
//                                               : dark(context)
//                                                   ? Theme.of(context).disabledColor
//                                                   : ColorUtil.lightGrey)),
//                                   child: Padding(
//                                     padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3.5),
//                                     child: Text(widget.model!.question!.options![index].option.toString(),
//                                         maxLines: 1,
//                                         overflow: TextOverflow.ellipsis,
//                                         style: Theme.of(context).textTheme.titleMedium!.copyWith(
//                                               fontSize: 12,
//                                             )),
//                                   ),
//                                 ),
//                               ),
                ,
              ],
            )
          ],
        ),
      ),
    );
  }

  InkWell pollOption(PollOption val, BuildContext context) {
    return InkWell(
      onTap: () {
        if (currentUser.value.id != null) {
          if (widget.model!.isVote != 0) {
            //  showCustomToast(context, 'Vote already registered');
          } else {
            if (currentUser.value.id == null) {
              Navigator.pushNamed(context, '/LoginPage');
            } else {
              isLoading = true;
              setState(() {});
              _saveVoting(val.id!.toInt());
            }
          }
        } else {
          Navigator.pushNamed(context, '/LoginPage');
        }
      },
      child: Stack(
        children: [
          Container(
            color: dark(context) ? Colors.white.withAlpha(14) : Colors.black.withAlpha(14),
            padding: EdgeInsets.symmetric(vertical: 4.0, horizontal: 6),
            margin: EdgeInsets.all(4),
            child: Center(
                child: Text(
              val.option ?? "",
              maxLines: 1,
            )),
          ),
          if (widget.model!.isVote != 0)
            Positioned(
                bottom: 4,
                left: 4,
                child: Container(
                  alignment: Alignment.centerLeft,
                  padding: const EdgeInsets.symmetric(vertical: 4),
                  width: ((size(context).width / 3.1)) * (val.percentage!.toInt() / 100),
                  color: dark(context)
                      ? Colors.white.customOpacity(0.3)
                      : Theme.of(context).primaryColor.customOpacity(0.3),
                  child: const Text(''),
                )),
          if (widget.model!.isVote != 0)
            Positioned(
                right: languageCode.value.pos == "rtl" ? null : 8,
                left: languageCode.value.pos == "rtl" ? 8 : null,
                top: 10,
                child: Text("${val.percentage.toStringAsFixed(0)}%",
                    style: const TextStyle(
                        fontFamily: 'Roboto', fontSize: 12, letterSpacing: 1, fontWeight: FontWeight.w600)))
        ],
      ),
    );
  }
}

class PollPercent extends StatelessWidget {
  const PollPercent({
    super.key,
    this.fraction,
    this.poll,
    this.percText,
  });

  final int? poll;
  final String? percText;
  final double? fraction;

  @override
  Widget build(BuildContext context) {
    return AnimatedContainer(
        duration: const Duration(microseconds: 200),
        curve: Curves.easeInOut,
        decoration: BoxDecoration(
            color: poll == 0 ? ColorUtil.textblack : null,
            gradient: poll == 0 ? null : primaryGradient(context),
            borderRadius: poll == 0
                ? const BorderRadius.only(topRight: Radius.circular(30), bottomRight: Radius.circular(30))
                : const BorderRadius.only(topLeft: Radius.circular(30), bottomLeft: Radius.circular(30))),
        alignment: Alignment.center,
        padding: EdgeInsets.symmetric(vertical: 7, horizontal: fraction! < 20.0 ? 2 : 0),
        child: Text(percText ?? '89%',
            style: const TextStyle(
              fontFamily: 'Roboto',
              color: Colors.white,
              height: 0,
              fontSize: 16,
            )));
  }
}

class BlogPollWrap extends StatefulWidget {
  const BlogPollWrap({super.key, this.isLoading = false, required this.onPageTap});
  final bool isLoading;
  final ValueChanged<int> onPageTap;

  @override
  State<BlogPollWrap> createState() => _BlogPollWrapState();
}

class _BlogPollWrapState extends State<BlogPollWrap> {
  PageController pageController = PageController(viewportFraction: 0.85);
  int currIndex = 0;
  @override
  Widget build(BuildContext context) {
    return SizedBox(
        height: widget.isLoading == false && blogPolls == null ? 0 : size(context).height * 0.43,
        child: Column(
          children: [
            Expanded(
              flex: 2,
              child: PageView(
                controller: pageController,
                padEnds: false,
                onPageChanged: (value) {
                  currIndex = value;
                  setState(() {});
                },
                children: widget.isLoading == true
                    ? [...List.generate(5, (index) => ShimmerLoader())]
                    : widget.isLoading == false && blogPolls == null
                        ? []
                        : [
                            ...List.generate(blogPolls!.blogs.length, (index) {
                              var blogPoll = blogPolls!.blogs[index];
                              return GestureDetector(
                                onTap: () {
                                  widget.onPageTap(index);
                                  setState(() {});
                                },
                                child: Padding(
                                  padding: EdgeInsets.only(
                                      left: index == 0 ? 15.0 : 8,
                                      right: index == blogPolls!.blogs.length - 1 ? 15 : 8),
                                  child: CachedNetworkImage(
                                    imageUrl: blogPoll.images != null && blogPoll.images!.isNotEmpty
                                        ? blogPoll.images![0]
                                        : '',
                                    fit: BoxFit.cover,
                                    errorWidget: (ctx, ff, index) {
                                      return RectangleAppIcon(
                                        width: size(context).width / 1.33,
                                      );
                                    },
                                  ),
                                ),
                              );
                            }),
                          ],
              ),
            ),
            Container(
                alignment: Alignment.center,
                height: 60,
                padding: EdgeInsets.symmetric(horizontal: 15),
                child: Text(
                  blogPolls!.blogs[currIndex].title ?? "",
                  maxLines: 3,
                )),
            SizedBox(height: 4),
            if (blogPolls!.blogs[currIndex].question != null)
              BlogPoll(
                  isStaticHeight: true,
                  pollKey: GlobalKey(debugLabel: "${currIndex}"),
                  model: blogPolls!.blogs[currIndex],
                  onChanged: (val) {}),
          ],
        ));
  }
}

class QuoteWrap extends StatefulWidget {
  const QuoteWrap({super.key, this.isLoading = false, required this.onPageTap});
  final bool isLoading;
  final ValueChanged<int> onPageTap;

  @override
  State<QuoteWrap> createState() => _QuoteWrapState();
}

class _QuoteWrapState extends State<QuoteWrap> {
  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(builder: (context, constraints) {
      return Container(
        color: Colors.transparent,
        height: widget.isLoading == false && quotes == null ? 0 : size(context).height * 0.3,
        child: SingleChildScrollView(
          scrollDirection: Axis.horizontal,
          child: Row(
            children: widget.isLoading == true && quotes == null
                ? [...List.generate(5, (index) => ShimmerLoader())]
                : widget.isLoading == false && quotes == null
                    ? []
                    : [
                        SizedBox(width: 16),
                        ...List.generate(quotes!.blogs.length, (index) {
                          var quote = quotes!.blogs[index];
                          return GestureDetector(
                            onTap: () {
                              blogListHolder.setList(quotes as DataModel);
                              blogListHolder.setBlogType(BlogType.category);
                              widget.onPageTap(index);
                              setState(() {});
                            },
                            child: Container(
                              width: size(context).width / 2.5,
                              margin: EdgeInsets.only(right: 6),
                              padding: const EdgeInsets.all(2.0),
                              child: ClipRRect(
                                borderRadius: BorderRadius.circular(8),
                                child: CachedNetworkImage(
                                  fit: BoxFit.fitHeight,
                                  imageUrl: (quote.images != null && quote.images!.isNotEmpty
                                      ? quote.images![0]
                                      : quote.backgroundImage),
                                  errorWidget: (ctc, d, s) {
                                    return AppIcon(
                                      isHandlerImage: true,
                                      fit: BoxFit.cover,
                                    );
                                  },
                                ),
                              ),
                            ),
                          );
                        }),
                        SizedBox(width: 16),
                      ],
          ),
        ),
      );
    });
  }
}

class SafeCustomOverscrollPhysics extends ScrollPhysics {
  final VoidCallback onOverscroll;

  const SafeCustomOverscrollPhysics({
    required this.onOverscroll,
    ScrollPhysics? parent,
  }) : super(parent: parent);

  @override
  SafeCustomOverscrollPhysics applyTo(ScrollPhysics? ancestor) {
    return SafeCustomOverscrollPhysics(
      onOverscroll: onOverscroll,
      parent: buildParent(ancestor),
    );
  }

  @override
  double applyBoundaryConditions(ScrollMetrics position, double value) {
    // 1. Handle overscroll at the bottom
    if (value > position.maxScrollExtent && position.pixels >= position.maxScrollExtent) {
      final overscroll = value - position.maxScrollExtent;
      // Only allow overscroll if the parent physics would allow it
      final parentOverscroll = super.applyBoundaryConditions(position, value);
      if (parentOverscroll != 0.0) {
        WidgetsBinding.instance.addPostFrameCallback((_) => onOverscroll());
      }
      // Return the minimum (most restrictive) overscroll value
      return overscroll.abs() > parentOverscroll.abs() ? parentOverscroll : overscroll;
    }

    // 2. Handle overscroll at the top
    if (value < position.minScrollExtent && position.pixels <= position.minScrollExtent) {
      final overscroll = value - position.minScrollExtent;
      final parentOverscroll = super.applyBoundaryConditions(position, value);
      return overscroll.abs() > parentOverscroll.abs() ? parentOverscroll : overscroll;
    }

    // 3. No overscroll - delegate to parent physics
    return super.applyBoundaryConditions(position, value);
  }
}
