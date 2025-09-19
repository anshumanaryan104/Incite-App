
import 'dart:developer';

import 'package:flutter/material.dart';
import 'package:incite/api_controller/news_repo.dart';
import 'package:incite/pages/main/dashboard.dart';
import 'package:incite/pages/main/widgets/share.dart';
import 'package:incite/splash_screen.dart';
import 'package:incite/urls/url.dart';
import 'package:incite/utils/image_util.dart';
import 'package:incite/utils/nav_util.dart';
import 'package:incite/widgets/app_bar.dart';
import 'package:incite/widgets/loader.dart';
import 'package:incite/widgets/pdf.dart';
import 'package:share_plus/share_plus.dart';

import '../api_controller/user_controller.dart';
import '../widgets/live_news.dart';

class EnewsPage extends StatefulWidget {
  const EnewsPage({super.key,this.id});
  final int? id;

  @override
  State<EnewsPage> createState() => _EnewsPageState();
}

class _EnewsPageState extends State<EnewsPage> {
  late bool isShare = false;


  @override
  void initState() {
     
      if(widget.id != null){
        isShare = true;
      } else {
        isShare = false;
      }
      getENews(context).then((value)async {
        
        isShare = false;
        setState(() { });

        if(widget.id != null && value.isNotEmpty){
          log(widget.id.toString());
          for (var e in value) {
            if(e.id == widget.id){

            log(e.id.toString());
               Navigator.push(context,PagingTransform(widget:  PdfViewWidget(
                  model: e,
                )));
            }
          }
        }
    });
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    return  CustomLoader(
      isLoading: isShare,
      child: Dismissible(
        key: UniqueKey(),
        direction: DismissDirection.startToEnd,
        onDismissed: (direction) {
          if (direction == DismissDirection.startToEnd &&  !prefs!.containsKey('id')) {
            Navigator.pop(context);
          }else if(widget.id != null && prefs!.containsKey('id')){
              prefs!.remove('id');
               Navigator.pushAndRemoveUntil(context, MaterialPageRoute(builder: (context)=> const DashboardPage(index: 0)),(route)=> false);
          }
        },
        child:Scaffold(
          body: CustomScrollView(
            slivers: [
               CommonAppbar(
                title:allMessages.value.eNews ?? 'E-news',
                onBack: () {
                  if(!prefs!.containsKey('id')) {
                    Navigator.pop(context);
                  } else if(widget.id != null && prefs!.containsKey('id')){
                      prefs!.remove('id');
                      Navigator.pushAndRemoveUntil(context, MaterialPageRoute(builder: (context)=> const DashboardPage(index: 0)),(route)=> false);
                  }
                },
              ),
               const SliverToBoxAdapter(
                child: SizedBox(height: 10),
              ),
              SliverToBoxAdapter(
                child: Column(
                  children: eNews.isEmpty ? [
                     ...List.generate(5, (index) => const Padding(
                       padding:  EdgeInsets.symmetric(horizontal: 20),
                       child: ListShimmer(),
                     ))
                  ] : [
                    ...eNews.map((e) => LiveWidget(
                      padding: const EdgeInsets.only(top: 16,bottom: 16,),
                      title: e.name,
                     
                      onShare : () async {
                        isShare = true;
                        setState((){});
                        await downloadImage(e.image ?? allSettings.value.appLogo ?? "").then((image) async {
                           shareImage(image ?? XFile(''),"${Urls.baseServer}e-news/${e.id}");
                             isShare = false;
                            setState((){});
                        });
                      },
                      fontWeight: FontWeight.w500,
                      image: e.image,
                      onTap: () {
                        Navigator.push(context,PagingTransform(widget:  PdfViewWidget(
                          model: e,
                        )));
                      },
                    )
                    )
                  ],
                ),
              )
            ],
          ),
        ),
      ),
    );
  }
}
