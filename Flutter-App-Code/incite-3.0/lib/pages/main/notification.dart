import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:incite/api_controller/app_provider.dart';
import 'package:incite/api_controller/user_controller.dart';
import 'package:incite/main.dart';
import 'package:incite/model/blog.dart';
import 'package:incite/pages/search/widget/list_contain.dart';
import 'package:incite/splash_screen.dart';
import 'package:incite/utils/color_util.dart';
import 'package:incite/utils/theme_util.dart';
import 'package:incite/widgets/anim_util.dart';
import 'package:incite/widgets/app_bar.dart';
import 'package:incite/widgets/loader.dart';
import 'package:provider/provider.dart';

class NotificationScreen extends StatefulWidget {
  const NotificationScreen({super.key});

  @override
  State<NotificationScreen> createState() => _NotificationScreenState();
}

class _NotificationScreenState extends State<NotificationScreen> {
  var isLoad = false;
  
  List<Blog> notifications = [];

  @override
  void initState() {
   WidgetsBinding.instance.addPostFrameCallback((e){
    //  if (prefs!.containsKey('notifications')) {
    //     var result = jsonDecode(prefs!.getString('notifications').toString());
    //     for (var e in result) {
    //       e['id'] = e['id'] != null ? int.parse(e['id'].toString()) : 0;
    //        notifications.add(Blog.fromJson(e));
    //      }
    //       notifications.reversed.toList();
    //       setState(() { });
    //    }
      
   });
   
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
  
    notifications.clear();
      if (prefs!.containsKey('notifications')) {
      var result = jsonDecode(prefs!.getString('notifications').toString());
      for (var e in result) {
        e['id'] = e['id'] != null ? int.parse(e['id'].toString()) : 0;
          notifications.add(Blog.fromJson(e));
        }
        notifications.reversed.toList();
      }
    return Consumer<AppProvider>(
      builder: (context,provider,child) {
        return CustomLoader(
          isLoading: isLoad,
          key: ValueKey(notifications.length),
          child: Scaffold(
                body: CustomScrollView(
                  slivers: [
                     CommonAppbar(title:allMessages.value.notifications ?? 'My Saved Stories',
                     actions: [
                      InkResponse(
                        onTap: (){
                          showCustomDialog(context: context,dismissible: true,text: 'Do you want to delete all notifications ?',
                          isTwoButton: true,onTap: (){
                             prefs!.remove('notifications');
                             notifications.clear();
                             setState(() {});
                             Navigator.pop(context);
                          });
                        },
                        child: const Padding(
                          padding: EdgeInsets.all(15.0),
                          child: Text('Clear All'),
                        ),
                      )
                     ],
                     isPinned: true),
                     SliverPadding(
                         padding: const EdgeInsets.only(bottom: 12,top: 10),
                         sliver: SliverToBoxAdapter(  
                           child: notifications.isEmpty && isLoad==false ?
                           SizedBox(
                            height: size(context).height/1.5,
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                  AnimationFadeScale(
                                child: Image.asset('assets/images/confuse.png',
                                width: 70,
                                height: 70,
                                color:dark(context) ? ColorUtil.white : ColorUtil.blackGrey,
                                ),
                              ),
                              const SizedBox(height: 20),
                               Text(allMessages.value.noResultFound ?? 'No Bookmark Post Found',
                               style:const TextStyle(
                                fontFamily: 'Roboto',
                                fontSize: 20,
                                fontWeight: FontWeight.w500
                              ))
                              ],
                            ),
                           ) :StreamBuilder<dynamic>(
                           stream: selectNotificationStream.stream,
                             builder: (context,snapshot) {
                               return Column(
                                mainAxisAlignment: provider.bookmarks.isEmpty ? 
                                MainAxisAlignment.center : MainAxisAlignment.start,
                                children:   [
                                   ...notifications.reversed.toList().asMap().entries.map((e){ 
                                        return ListWrapper(
                                          key: ValueKey(e.key),
                                          e: e.value,
                                          showBookmark: false,
                                          isBookmark: false,
                                          // onChanged: (value) {
                                          //   provider.setBookmark(blog: e.value);
                                          //   setState(() {   });
                                          // },
                                          date: e.value.updatedAt,
                                          index: e.key,
                                          onTap: () async { 
                                            isLoad = true;
                                            setState(() { });
                                            await blogDetail(e.value.id.toString()).then((e){
                                             Navigator.pushNamed(context,'/BlogPage',arguments: [e,false]).then((value) {
                                               isLoad = false;
                                               setState(() { });
                                            });
                                           });
                                        }); 
                                    })
                                ],
                               );
                             }
                           ),
                         ),
                       )
                  ],
                ),
              ),
      );
     });
  }
}