import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:incite/widgets/short_video_controller.dart';
import 'package:video_player/video_player.dart';

class FullScreenVideo extends StatefulWidget {
  const FullScreenVideo({super.key, required this.controller});
  final VideoPlayerController controller;

  @override
  State<FullScreenVideo> createState() => _FullScreenVideoState();
}

class _FullScreenVideoState extends State<FullScreenVideo> {
  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (val, result) {
        if (val) {
          return;
        }

        SystemChrome.setPreferredOrientations([
          DeviceOrientation.portraitUp,
          DeviceOrientation.portraitDown,
        ]);
        Navigator.pop(context);
      },
      child: Scaffold(
        body: ShortPlayAnyVideo(
          controller: widget.controller,
          aspectRatio: 16 / 9,
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
        ),
      ),
    );
  }
}
