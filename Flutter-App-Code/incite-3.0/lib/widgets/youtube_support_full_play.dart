import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:incite/model/blog.dart';
import 'package:incite/utils/app_theme.dart';
import 'package:incite/widgets/back.dart';
import 'package:incite/widgets/incite_video_player.dart';
import 'package:incite/widgets/short_video_controller.dart';
import 'package:video_player/video_player.dart';
import 'package:webviewtube/webviewtube.dart';

class YoutubeSupportPlayScreen extends StatefulWidget {
  const YoutubeSupportPlayScreen({super.key,this.startAt,this.isWebViewPlayer=false,this.webviewtubeController, this.controller,this.isShortVideo=false, this.blog});
  final VideoPlayerController? controller;
  final WebviewtubeController? webviewtubeController;
  final Blog? blog;
  final bool? isWebViewPlayer;
  final bool isShortVideo;
  final int? startAt;

  @override
  State<YoutubeSupportPlayScreen> createState() => _YoutubeSupportPlayScreenState();
}

class _YoutubeSupportPlayScreenState extends State<YoutubeSupportPlayScreen> {
  @override
  Widget build(BuildContext context) {
     var orientation = MediaQuery.of(context).orientation == Orientation.landscape;
    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (val, c){
        if(val){
          return;
        }
         
          var orientation2 = MediaQuery.of(context).orientation == Orientation.landscape;
          
         if (widget.isWebViewPlayer == true) {

           if (orientation2 == true) {
            SystemChrome.setPreferredOrientations([
              DeviceOrientation.portraitUp,
              DeviceOrientation.portraitDown,
            ]);
             Navigator.pop(context);
           }
           
         } else {
          
          if (orientation2 == true) {
            SystemChrome.setPreferredOrientations([
              DeviceOrientation.portraitUp,
              DeviceOrientation.portraitDown,
            ]);
           } else {
            SystemChrome.setPreferredOrientations([
              DeviceOrientation.portraitUp,
              DeviceOrientation.portraitDown,
            ]);
             Navigator.pop(context);
           }
         }

      },
      child: Scaffold(
        backgroundColor: Colors.black,
        appBar:  orientation == true ? null :AppBar(
          leading: const Backbut(),
          leadingWidth: 60,
          backgroundColor: Colors.transparent,
          toolbarHeight: 50,
        ),
        body: Padding(
          padding:  EdgeInsets.only(bottom:orientation == true ? 0 : 100),
          child: Center(
            child: widget.isWebViewPlayer == true 
            ? 
              PlayAnyVideoPlayer(
                key: ValueKey(widget.blog!.id),
                
                model: widget.blog,
                // startAt: widget.webviewtubeController!.value.position.inSeconds,
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
                setState(() {   });
              },
              startAt: widget.startAt,
              )
             : ShortPlayAnyVideo(
              // controller: widget.controller,
              model: widget.blog,
              isShortVideo: widget.isShortVideo,
              isAutoPlay: appThemeModel.value.isAutoPlay.value,
              aspectRatio: widget.isShortVideo ? 9/16 : 16/9,
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
        ),
      ),
    );
  }
}