import 'dart:convert';
import 'dart:io';

import 'package:flutter/material.dart';
import 'package:incite/api_controller/app_provider.dart';
import 'package:incite/model/blog.dart';
import 'package:incite/pages/interests/widgets/radius_wrap.dart';
import 'package:incite/splash_screen.dart';
import 'package:incite/utils/app_theme.dart';
import 'package:incite/widgets/back.dart';
import 'package:incite/widgets/custom_toast.dart';
import 'package:incite/widgets/loader.dart';
import 'package:provider/provider.dart';
import '../../api_controller/user_controller.dart';
import "package:http/http.dart" as http;
import '../../urls/url.dart';
import '../../utils/rgbo_to_hex.dart';
import '../../widgets/anim_util.dart';
import '../../widgets/button.dart';

class SaveInterest extends StatefulWidget {
  const SaveInterest({super.key, this.isDrawer = false});
  final bool isDrawer;

  @override
  State<SaveInterest> createState() => _SaveInterestState();
}

class _SaveInterestState extends State<SaveInterest> {
  List selected = [];

  bool load = false;
  List<int> selectedFeeds = [];

  @override
  void initState() {
    WidgetsBinding.instance.addPostFrameCallback((timeStamp) async {
      await getCacheCategoryData().then((value) {
        if (value != null) {
          for (var i = 0; i < value.categories!.length; i++) {
            if (value.categories![i].isFeed == true) {
              selectedFeeds.add(value.categories![i].id as int);
            }
          }
          setState(() {});
        }
      });
    });
    super.initState();
  }

  Future setCategory(AppProvider provider, {bool isExit = false}) async {
    provider.selectedInterests(selectedFeeds);

    if (provider.selectedFeed.length < 3) {
      if (isExit == true) {
        Navigator.pop(context);
      }
      showCustomToast(context, allMessages.value.minimum3Select ?? "Minimum 3 should be selected");
    } else {
      if (true) {
        final formMap = jsonEncode({
          "category_ids": provider.selectedFeed,
          "user_id": currentUser.value.id,
          "device_id": prefs?.getString('device_id') ?? 'unknown_device'
        });
        try {
          setState(() {
            load = true;
          });
          var url = "${Urls.baseUrl}add-feed";

          var headers = {
            HttpHeaders.contentTypeHeader: "application/json",
            "language-code": languageCode.value.language ?? "en",
          };

          // Add api-token only if user is logged in and has token
          if (currentUser.value.id != null && currentUser.value.apiToken != null) {
            headers["api-token"] = currentUser.value.apiToken!;
          }

          var result = await http.post(
            Uri.parse(url),
            headers: headers,
            body: formMap,
          );

          Map data = json.decode(result.body);

          // Redirect to main page regardless of API success/failure
          // User has selected their interests, that's what matters
          if (widget.isDrawer) {
            if (result.statusCode == 200 && data['success'] == true) {
              showCustomToast(context, data['message']);
            }
            await provider.getCategory(headCall: false).then((value) {
              setState(() {
                load = false;
              });
              Navigator.pushNamedAndRemoveUntil(context, '/MainPage', (route) => false, arguments: 0);
            });
          } else {
            // New user signup flow
            if (result.statusCode == 200 && data['success'] == true) {
              showCustomToast(context, data['message'] ?? 'Interests saved!');
            } else {
              showCustomToast(context, 'Interests saved locally!');
            }
            setState(() {
              currentUser.value.isNewUser = true;
              load = false;
            });
            await provider.getCategory().then((value) {
              Navigator.pushNamedAndRemoveUntil(context, '/MainPage', (route) => false, arguments: 1);
            });
          }
        } catch (e) {
          debugPrint("Error saving interests: $e");
          // Even on error, redirect user to main page
          showCustomToast(context, 'Continuing to app...');
          setState(() {
            load = false;
          });
          await provider.getCategory().then((value) {
            Navigator.pushNamedAndRemoveUntil(context, '/MainPage', (route) => false, arguments: widget.isDrawer ? 0 : 1);
          });
        }
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    var provider = Provider.of<AppProvider>(context, listen: false);

    return CustomLoader(
      isLoading: load,
      child: Scaffold(
        body: SafeArea(
          top: false,
          bottom: true,
          child: LayoutBuilder(
            builder: (context, constraint) {
              return SingleChildScrollView(
                child: ConstrainedBox(
                  constraints: BoxConstraints(minHeight: constraint.maxHeight),
                  child: IntrinsicHeight(
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 24),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const SizedBox(height: kToolbarHeight),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Backbut(
                                onTap: () async {
                                  if (widget.isDrawer) {
                                    Navigator.pop(context);
                                  } else {
                                    // Skip interests and go to main page
                                    Navigator.pushNamedAndRemoveUntil(
                                        context, '/MainPage', (route) => false, arguments: 0);
                                  }
                                },
                              ),
                              if (!widget.isDrawer)
                                TextButton(
                                  onPressed: () {
                                    Navigator.pushNamedAndRemoveUntil(
                                        context, '/MainPage', (route) => false, arguments: 0);
                                  },
                                  child: Text(
                                    'Skip',
                                    style: TextStyle(
                                      fontSize: 16,
                                      fontWeight: FontWeight.w500,
                                    ),
                                  ),
                                ),
                            ],
                          ),
                          SizedBox(height: widget.isDrawer ? 20 : 0),
                          AnimationFadeSlide(
                            duration: 500,
                            child: Text(
                              widget.isDrawer
                                  ? allMessages.value.editSaveInterests ??
                                      'Your interests for better content & experience.'
                                  : allMessages.value.saveYourInterestsForBetterContentExperience ??
                                      'Save your interests for better content & experience.',
                              textAlign: TextAlign.center,
                              style: TextStyle(
                                fontFamily: 'Roboto',
                                fontSize: widget.isDrawer ? 24 : 30,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ),
                          const SizedBox(height: 30),
                          Wrap(
                            spacing: 10,
                            runSpacing: 16,
                            children: [
                              ...provider.blog!.categories!.asMap().entries.map(
                                (e) => RadiusBox(
                                  index: e.key,
                                  color: hexToRgb(e.value.color.toString()),
                                  isGradient: true,
                                  onTap: () {
                                    if (selectedFeeds.contains(e.value.id as int)) {
                                      selectedFeeds.remove(e.value.id as int);
                                    } else {
                                      selectedFeeds.add(e.value.id as int);
                                    }
                                    setState(() {});
                                  },
                                  title: e.value.name.toString(),
                                  isSelected: selectedFeeds.contains(e.value.id as int),
                                ),
                              ),
                            ],
                          ),
                          const Spacer(),
                          ElevateButton(
                            color:
                                selectedFeeds.length < 3
                                    ? Theme.of(context).primaryColor.customOpacity(0.3)
                                    : Theme.of(context).primaryColor,
                            style: const TextStyle(
                              fontFamily: 'Poppins',
                              fontSize: 18,
                              color: Colors.white,
                              fontWeight: FontWeight.w500,
                            ),
                            onTap: () async {
                              await setCategory(provider);
                            },
                            text:
                                widget.isDrawer
                                    ? allMessages.value.editButtonInterests ?? 'Save Interests'
                                    : allMessages.value.saveInterests ?? 'Save Interests',
                          ),
                          if (Platform.isAndroid) const SizedBox(height: 16),
                        ],
                      ),
                    ),
                  ),
                ),
              );
            },
          ),
        ),
      ),
    );
  }

  Future<DataCollection?> getCacheCategoryData() async {
    return DataCollection.fromJson(jsonDecode(prefs!.getString('collection').toString()));
  }
}
