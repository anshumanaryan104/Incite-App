import 'package:flutter/material.dart';
import 'package:incite/utils/app_theme.dart';

// ignore: unused_element
class VideoQualitySelectorMob extends StatelessWidget {
  final ValueChanged? onChanged;
  final List<int> quality;
  final int initSelectedQuality;

  const VideoQualitySelectorMob({
    super.key,
    required this.onChanged,
    required this.initSelectedQuality,
    required this.quality,
  });

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      child: Column(mainAxisSize: MainAxisSize.min, crossAxisAlignment: CrossAxisAlignment.start, children: [
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16),
          child: Text('Quality for the video : ${initSelectedQuality}p',
              style: const TextStyle(fontSize: 16, fontFamily: 'Roboto', color: Colors.grey)),
        ),
        const SizedBox(height: 12),
        ...quality.map(
          (e) => ListTile(
            title: Text('${e}p'),
            selectedColor: Theme.of(context).primaryColor,
            selectedTileColor: Theme.of(context).primaryColor.customOpacity(0.2),
            selected: initSelectedQuality == e,
            leading: initSelectedQuality == e ? const Icon(Icons.done_rounded) : const SizedBox(),
            onTap: () {
              onChanged != null ? onChanged!(e) : Navigator.pop(context, e);

              // podCtr.changeVideoQuality(e.quality);
            },
          ),
        ),
      ]),
    );
  }
}
