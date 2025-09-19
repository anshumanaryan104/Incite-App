import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_svg/svg.dart';
import 'package:incite/api_controller/app_provider.dart';
import 'package:incite/utils/app_theme.dart';
import 'package:incite/utils/color_util.dart';
import 'package:incite/utils/image_util.dart';
import 'package:incite/utils/theme_util.dart';
import 'package:incite/widgets/anim_util.dart';
import 'package:incite/widgets/gradient.dart';
import 'package:incite/widgets/tap.dart';
import 'package:provider/provider.dart';

import '../../../api_controller/user_controller.dart';
import '../../../model/blog.dart';
import '../../../utils/time_util.dart';
import '../../../widgets/custom_toast.dart';

class ListWrapper extends StatelessWidget {
  const ListWrapper({
    super.key,
    required this.onTap,
    this.isSearch = false,
    this.onChanged,
    this.isBookmark = false,
    this.showBookmark = true,
    required this.e,
    required this.index,
    this.date,
  });

  final Blog e;
  final bool isBookmark, isSearch, showBookmark;
  final int index;
  final String? date;
  final ValueChanged? onChanged;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    var size = MediaQuery.of(context).size;
    var provider = Provider.of<AppProvider>(context, listen: false);
    return TapInk(
      splash: ColorUtil.whiteGrey,
      onTap: onTap,
      child: Container(
        width: size.width,
        height: 85,
        padding: const EdgeInsets.symmetric(horizontal: 24),
        margin: const EdgeInsets.only(top: 11, bottom: 11),
        child: Row(
          children: [
            AnimationFadeScale(
              duration: index * 150,
              child:
                  e.images!.isNotEmpty
                      ? CircleAvatar(
                        radius: 32,
                        backgroundImage: CachedNetworkImageProvider(e.images![0] ?? ''),
                      )
                      : CircleAvatar(radius: 32, backgroundImage: AssetImage(Img.logo)),
            ),
            const SizedBox(width: 15),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: AnimationFadeSlide(
                          dx: (index * 0.4),
                          duration: index * 150,
                          child: Text(
                            e.title ?? 'How analysis essays are the new analysis essays',
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(
                              fontFamily: 'Roboto',
                              fontSize: 15,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      if (showBookmark == true)
                        InkResponse(
                          splashColor: Theme.of(context).primaryColor.customOpacity(0.4),
                          radius: 24,
                          onTap:
                              currentUser.value.id == null
                                  ? () {
                                    Navigator.pushNamed(context, '/LoginPage');
                                  }
                                  : isSearch
                                  ? () {
                                    if (provider.permanentIds.contains(e.id)) {
                                      provider.removeBookmarkData(e.id!.toInt());
                                      showCustomToast(
                                        context,
                                        allMessages.value.bookmarkRemove ?? 'Bookmark Removed',
                                      );
                                    } else {
                                      provider.addBookmarkData(e.id!.toInt());
                                      showCustomToast(
                                        context,
                                        allMessages.value.bookmarkSave ?? 'Bookmark Saved',
                                      );
                                    }
                                    onChanged!(e.isBookmark);
                                  }
                                  : () {
                                    if (isBookmark == true) {
                                      provider.removeBookmarkData(e.id!.toInt());
                                      showCustomToast(
                                        context,
                                        allMessages.value.bookmarkRemove ?? 'Bookmark Removed',
                                      );
                                    }
                                    onChanged!(e);
                                  },
                          child: SvgPicture.asset(
                            provider.permanentIds.contains(e.id) ? SvgImg.fillBook : SvgImg.bookmark,
                            width: 20,
                            height: 20,
                            key: ValueKey(provider.permanentIds.contains(e.id)),
                            colorFilter: ColorFilter.mode(
                              isBlack(Theme.of(context).primaryColor)
                                  ? Colors.white
                                  : Theme.of(context).primaryColor,
                              BlendMode.srcIn,
                            ),
                          ),
                        ),
                    ],
                  ),
                  const Spacer(),
                  Expanded(
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Expanded(
                          child: Row(
                            children: [
                              Expanded(
                                child: GradientText(
                                  e.categoryName ?? 'Business',
                                  gradient:
                                      dark(context) ? darkPrimaryGradient(context) : primaryGradient(context),
                                  style: const TextStyle(
                                    overflow: TextOverflow.ellipsis,
                                    fontFamily: 'Roboto',
                                    fontSize: 12,
                                  ),
                                ),
                              ),
                              Padding(
                                padding: const EdgeInsets.symmetric(horizontal: 6),
                                child: CircleAvatar(
                                  radius: 2,
                                  backgroundColor: dark(context) ? ColorUtil.whiteGrey : ColorUtil.textblack,
                                ),
                              ),
                              Text(
                                e.scheduleDate == null
                                    ? ''
                                    : date != null
                                    ? formatTimeAgo(DateTime.tryParse(date ?? "") ?? DateTime.now())
                                    : timeFormat(DateTime.tryParse(e.scheduleDate!.toIso8601String())),
                                style: TextStyle(
                                  fontFamily: 'Roboto',
                                  fontSize: 12,
                                  color: dark(context) ? ColorUtil.whiteGrey : ColorUtil.textblack,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
