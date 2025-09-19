import 'package:flutter/material.dart';
import 'package:incite/api_controller/user_controller.dart';
import 'package:incite/utils/app_theme.dart';
import 'package:incite/utils/theme_util.dart';

import '../api_controller/user_controller.dart';

class DarkModeToggle extends StatefulWidget {
  const DarkModeToggle({super.key});

  @override
  State<DarkModeToggle> createState() => _DarkModeToggleState();
}

class _DarkModeToggleState extends State<DarkModeToggle> {
  @override
  Widget build(BuildContext context) {
    return Center(
      child: Container(
        width: size(context).width,
        height: size(context).height / 3,
        decoration: BoxDecoration(
          color: Theme.of(context).canvasColor,
          borderRadius: BorderRadius.circular(30),
        ),
        alignment: Alignment.center,
        margin: const EdgeInsets.symmetric(horizontal: 16),
        padding: const EdgeInsets.only(left: 24, right: 24, bottom: 16),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.end,
              children: [
                InkResponse(
                  onTap: () {
                    Navigator.pop(context);
                  },
                  child: const Icon(Icons.close_rounded),
                ),
              ],
            ),
            const SizedBox(height: 24),
            Text(
              allMessages.value.darkMode ?? "",
              style: Theme.of(
                context,
              ).textTheme.displaySmall!.copyWith(fontSize: 20, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 6),
            Text(
              allMessages.value.darkModeDescription ?? "",
              textAlign: TextAlign.center,
              style: Theme.of(context).textTheme.titleMedium!.copyWith(fontWeight: FontWeight.w400),
            ),
            const SizedBox(height: 24),
            Container(
              // width: size(context).width/1.5,
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(100),
                color: Theme.of(context).cardColor,
                boxShadow: [
                  BoxShadow(
                    blurRadius: 10,
                    color: dark(context) ? Colors.white.customOpacity(0.03) : Colors.black12,
                    offset: const Offset(0, 5),
                    spreadRadius: 2,
                  ),
                ],
              ),
              padding: const EdgeInsets.symmetric(vertical: 4, horizontal: 4),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Toggle(
                    text: allMessages.value.light,
                    isSelected: appThemeModel.value.isDarkModeEnabled.value == ThemeMode.light,
                    onTap: () {
                      toggleDarkMode(ThemeMode.light);
                      setState(() {});
                    },
                  ),
                  Toggle(
                    text: allMessages.value.system,
                    isSelected: appThemeModel.value.isDarkModeEnabled.value == ThemeMode.system,
                    onTap: () {
                      toggleDarkMode(ThemeMode.system);
                      setState(() {});
                    },
                  ),
                  Toggle(
                    text: allMessages.value.dark,
                    isSelected: appThemeModel.value.isDarkModeEnabled.value == ThemeMode.dark,
                    onTap: () {
                      toggleDarkMode(ThemeMode.dark);
                      setState(() {});
                    },
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

class Toggle extends StatelessWidget {
  const Toggle({super.key, this.text, this.isSelected, required this.onTap});

  final String? text;
  final VoidCallback onTap;
  final bool? isSelected;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 300),
        curve: Curves.easeIn,
        padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
        decoration: BoxDecoration(
          color: isSelected == true ? Theme.of(context).primaryColor : null,
          borderRadius: BorderRadius.circular(100),
        ),
        child: Text(
          text ?? "",
          textAlign: TextAlign.center,
          style: Theme.of(context).textTheme.bodyLarge!.copyWith(
                fontWeight: isSelected == true ? FontWeight.w600 : FontWeight.w400,
                color: isSelected == true
                    ? Colors.white
                    : dark(context)
                        ? Colors.white
                        : Colors.black,
                fontSize: isSelected == true ? 16 : 15,
              ),
        ),
      ),
    );
  }
}
