import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:flutter_html/flutter_html.dart';
import 'package:flutter_svg/svg.dart';
import 'package:incite/utils/theme_util.dart';
import 'package:incite/widgets/tap.dart';
import '../../../api_controller/app_provider.dart';
import '../../../api_controller/user_controller.dart';
import '../../../model/blog.dart';
import '../../../utils/color_util.dart';
import '../../../utils/image_util.dart';
import '../web_view.dart';

class PostFeatureWrap extends StatefulWidget {
  const PostFeatureWrap({
    super.key,
    this.isVolume = false,
    required this.onShare,
    required this.onBookmark,
    required this.model,
    required this.onVoice,
    this.isBookmarkContains = false,
    required this.provider,
    this.onAskAI,
  });

  final VoidCallback onShare, onVoice, onBookmark;
  final VoidCallback? onAskAI;
  final Blog model;
  final bool isVolume, isBookmarkContains;
  final AppProvider provider;

  @override
  State<PostFeatureWrap> createState() => _PostFeatureWrapState();
}

class _PostFeatureWrapState extends State<PostFeatureWrap> {
  bool isBookmark = false;

  @override
  Widget build(BuildContext context) {
    var colorFilter2 =
        colorFilterMode(context, color: dark(context) ? ColorUtil.white : Theme.of(context).primaryColor);
    return Row(
      children: [
        // All icons removed from top bar - Ask AI moved to floating button
        // Bookmark icon - commented out (not functional for now)
        // TapInk(
        //     key: ValueKey(widget.model.isBookmark),
        //     radius: 6,
        //     pad: 4,
        //     onTap: widget.onBookmark,
        //     child: SvgPicture.asset(
        //       widget.provider.permanentIds.contains(widget.model.id) ? SvgImg.fillBook : SvgImg.bookmark,
        //       width: 24,
        //       height: 24,
        //       colorFilter: colorFilter2,
        //     )),
        // Share icon - commented out (not functional for now)
        // TapInk(
        //     pad: 4,
        //     radius: 6,
        //     onTap: widget.onShare,
        //     child: SvgPicture.asset(SvgImg.share, width: 24, height: 24, colorFilter: colorFilter2)),
      ],
    );
  }
}

class TitleWidget extends StatelessWidget {
  const TitleWidget({
    super.key,
    this.color,
    required this.title,
    this.size,
  });

  final String? title;
  final double? size;
  final Color? color;

  @override
  Widget build(BuildContext context) {
    return Text(
      title ?? 'Apple Might Launch iPhone 16 Ultra By 2024 With New High-end Features; Report',
      style: TextStyle(
          fontFamily: 'Roboto', fontSize: size ?? 18, fontWeight: FontWeight.w500, height: 1.3, color: color),
    );
  }
}

class Description extends StatelessWidget {
  const Description(
      {super.key,
      required this.model,
      this.color,
      this.optionLength = 0,
      this.isPoll = false,
      this.maxLines});

  final Blog? model;
  final Color? color;
  final int? maxLines;
  final int optionLength;
  final bool isPoll;

//   String convertHtmlToPlainText(String htmlText) {
//   final tempElement = html.Element.html(htmlText);
//   return tempElement.innerText;
// }

  @override
  Widget build(BuildContext context) {
    var htmlPaddings = HtmlPaddings(
        left: HtmlPadding(0), right: HtmlPadding(0), top: HtmlPadding(0), bottom: HtmlPadding(0));
    return Html(
      data:
          model!.description ?? 'Lorem ipsum data can wiit the ckiodf iskf flkfgsdcadsad dbcd bhsdMN DVMMAM',
      style: {
        "body": Style(
            margin: Margins(
                left: Margin(0),
                right: Margin(0),
                blockStart: Margin(0),
                blockEnd: Margin(0),
                top: Margin(0),
                bottom: Margin(0)),
            fontSize: FontSize(defaultFontSize.value),
            fontFamily: 'Roboto',
            maxLines: maxLines ??
                (isPoll && optionLength == 4
                    ? 8
                    : isPoll && optionLength == 3
                        ? 8
                        : isPoll && optionLength == 2
                            ? 7
                            : null),
            color: dark(context) ? color ?? ColorUtil.textWhite : color ?? ColorUtil.textgrey,
            lineHeight: const LineHeight(1.4),
            textOverflow: maxLines != null
                ? TextOverflow.ellipsis
                : isPoll
                    ? TextOverflow.ellipsis
                    : TextOverflow.visible,
            padding: htmlPaddings),
        "p": Style(
            margin: Margins(
                left: Margin(0),
                right: Margin(0),
                blockStart: Margin(0),
                blockEnd: Margin(0),
                top: Margin(0),
                bottom: Margin(0)),
            fontSize: FontSize(defaultFontSize.value),
            fontFamily: 'Roboto',
            color: dark(context) ? color ?? ColorUtil.textWhite : color ?? ColorUtil.textgrey,
            //  padding: htmlPaddings
            lineHeight: const LineHeight(1.4)),
        "b": Style(
            fontSize: FontSize(defaultFontSize.value),
            fontFamily: 'Roboto',
            fontWeight: FontWeight.w600,
            color: dark(context) ? color ?? ColorUtil.textWhite : color ?? ColorUtil.textgrey,
            lineHeight: const LineHeight(1.4),
            padding: htmlPaddings),
        "i": Style(
            fontSize: FontSize(defaultFontSize.value),
            fontFamily: 'Roboto',
            fontStyle: FontStyle.italic,
            color: dark(context) ? color ?? ColorUtil.textWhite : color ?? ColorUtil.textgrey,
            lineHeight: const LineHeight(1.4),
            padding: htmlPaddings),
        "a": Style(
          fontSize: FontSize(defaultFontSize.value),
          fontFamily: 'Roboto',
          lineHeight: const LineHeight(1.4),
          textDecoration: TextDecoration.underline,
          color: isBlack(Theme.of(context).primaryColor) && dark(context)
              ? ColorUtil.textWhite
              : Theme.of(context).primaryColor,
        ),
        "li": Style(
            fontSize: FontSize(defaultFontSize.value),
            fontFamily: 'Roboto',
            color: dark(context) ? color ?? ColorUtil.textWhite : color ?? ColorUtil.textgrey,
            lineHeight: const LineHeight(1.4),
            padding: HtmlPaddings(
              left: HtmlPadding(12),
              right: HtmlPadding(12),
            )),
        "ul": Style(
            fontSize: FontSize(defaultFontSize.value),
            fontFamily: 'Roboto',
            color: dark(context) ? color ?? ColorUtil.textWhite : color ?? ColorUtil.textgrey,
            lineHeight: const LineHeight(1.4),
            padding: HtmlPaddings(
              left: HtmlPadding(12),
              right: HtmlPadding(12),
            )),
      },
      onLinkTap: (url, context1, element) {
        Navigator.push(
            context,
            CupertinoPageRoute(
                builder: (context) => CustomWebView(
                    onTap: () {
                      Navigator.pop(context);
                    },
                    url: url.toString())));
      },
    );
  }
}
