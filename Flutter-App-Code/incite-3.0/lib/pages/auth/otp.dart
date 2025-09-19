import 'dart:async';
import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:incite/api_controller/user_controller.dart';
import 'package:incite/utils/color_util.dart';
import 'package:incite/widgets/button.dart';
import '../../api_controller/repository.dart';
import '../../utils/image_util.dart';
import '../../utils/theme_util.dart';
import '../../widgets/custom_toast.dart';

import 'package:pinput/pinput.dart';
import 'package:flutter/foundation.dart' as Foundation;

TextEditingController enterOtp = TextEditingController();

class OtpScreen extends StatefulWidget {
  final String mail;

  const OtpScreen({super.key, this.mail = "mail@gmail.com"});

  @override
  State<OtpScreen> createState() => _OtpScreenState();
}

class _OtpScreenState extends State<OtpScreen> {
  int remainingTime = 60;
  Timer? timer;
  late UserProvider userProvider;
  String d1 = '', d2 = '', d3 = '', d4 = '';
  bool isLoad = false;

  @override
  void initState() {
    super.initState();
    userProvider = UserProvider();
    startTimer();
  }

  void startTimer() {
    timer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (mounted) {
        setState(() {
          remainingTime--;
        });

        if (remainingTime == 0) {
          timer.cancel();
        }
        //else {
        //   print("Time's up!");
        // }
      }
    });
  }

  @override
  void dispose() {
    timer!.cancel();
    super.dispose();
  }

  var controller = TextEditingController();
  var controller2 = TextEditingController();
  var controller3 = TextEditingController();
  var controller4 = TextEditingController();
  var controller5 = TextEditingController();

  FocusNode focus = FocusNode(),
      focus1 = FocusNode(),
      focus2 = FocusNode(),
      focus3 = FocusNode(),
      focus4 = FocusNode();

  @override
  Widget build(BuildContext context) {
    var size = MediaQuery.of(context).size;

    var otp = json.decode(emailData.toString())['data']['otp'];
    return Scaffold(
      resizeToAvoidBottomInset: false,
      body: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 6),
        child: Form(
          // key: userProvider.resetFormKey,
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Spacer(flex: 2),
              Image.asset(Img.logo, width: 100, height: 100),
              const SizedBox(height: 30),
              Text(allMessages.value.otp ?? 'Verify OTP',
                  style: Theme.of(context).textTheme.headlineSmall!.copyWith(
                      fontWeight: FontWeight.bold,
                      color: isBlack(Theme.of(context).primaryColor) && dark(context)
                          ? Colors.white
                          : Theme.of(context).primaryColor,
                      letterSpacing: 0.5)),
              const SizedBox(height: 12),
              Wrap(
                crossAxisAlignment: WrapCrossAlignment.center,
                alignment: WrapAlignment.center,
                children: [
                  Text(allMessages.value.otpDescription ?? 'Please enter the code we just sent to',
                      textAlign: TextAlign.center,
                      style: Theme.of(context)
                          .textTheme
                          .titleSmall!
                          .copyWith(fontWeight: FontWeight.w500, height: 1.4)),
                  Text(" ${widget.mail}",
                      textAlign: TextAlign.center,
                      style: Theme.of(context).textTheme.titleLarge!.copyWith(
                          fontWeight: FontWeight.w600,
                          fontSize: 16,
                          height: 1.4,
                          color: isBlack(Theme.of(context).primaryColor) && dark(context)
                              ? Colors.white
                              : Theme.of(context).primaryColor))
                ],
              ),
              const SizedBox(height: 50),
              SizedBox(width: size.width, height: 60, child: otppinfields(otp)),
              const SizedBox(height: 30),
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text(
                      isLoad == true
                          ? allMessages.value.loadingOTP ?? 'Preparing OTP...'
                          : remainingTime == 0
                              ? allMessages.value.resendCode ?? 'Resend Code ?'
                              : allMessages.value.resendCodeIn ?? 'Resend Code in ',
                      textAlign: TextAlign.center,
                      style: Theme.of(context).textTheme.bodyLarge!.copyWith(
                          fontWeight: FontWeight.w500,
                          color: isBlack(Theme.of(context).primaryColor) && dark(context)
                              ? Colors.white
                              : Theme.of(context).primaryColor)),
                  isLoad == true
                      ? const SizedBox()
                      : GestureDetector(
                          onTap: () {
                            userProvider.user.email = widget.mail;
                            isLoad = true;
                            setState(() {});
                            forgetPassword(userProvider.user, context).then((value) {
                              if (value == true) {
                                remainingTime = 60;
                                userProvider.user.otp = '';
                                startTimer();
                                isLoad = false;
                                showCustomToast(context, allMessages.value.otpSent ?? 'OTP sent');
                                setState(() {});
                              }
                            });
                          },
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Container(
                                padding: EdgeInsets.only(left: 2),
                                width: remainingTime == 0 ? 90 : 30,
                                child: Text(
                                    remainingTime == 0
                                        ? allMessages.value.sendAgain ?? 'Send Again'
                                        : remainingTime == 60
                                            ? '1 : '
                                            : '0 : ',
                                    textAlign: TextAlign.center,
                                    style: Theme.of(context).textTheme.bodyLarge!.copyWith(
                                        fontWeight: FontWeight.w600,
                                        color: isBlack(Theme.of(context).primaryColor) && dark(context)
                                            ? Colors.white
                                            : Theme.of(context).primaryColor,
                                        decoration: remainingTime == 0
                                            ? TextDecoration.underline
                                            : TextDecoration.none)),
                              ),
                              if (remainingTime != 0)
                                Container(
                                    width: remainingTime == 0 ? 70 : 50,
                                    alignment: Alignment.centerLeft,
                                    padding: EdgeInsets.only(left: 2),
                                    child: Text(
                                        remainingTime == 0
                                            ? ''
                                            : remainingTime == 60
                                                ? '00'
                                                : remainingTime < 10
                                                    ? '0$remainingTime'
                                                    : '${remainingTime}',
                                        textAlign: TextAlign.center,
                                        style: Theme.of(context).textTheme.bodyLarge!.copyWith(
                                            fontWeight: FontWeight.w600,
                                            color: isBlack(Theme.of(context).primaryColor) && dark(context)
                                                ? Colors.white
                                                : Theme.of(context).primaryColor))),
                            ],
                          ),
                        )
                ],
              ),
              const SizedBox(height: 32),
              ElevateButton(
                  onTap: () async {
                    if (remainingTime == 0) {
                      showCustomToast(context, allMessages.value.otpExpired ?? 'OTP is expired');
                    } else if (userProvider.user.otp == otp.toString()) {
                      showCustomToast(context, allMessages.value.otpVerified ?? 'OTP verified');
                      userProvider.user.otp = '';
                      enterOtp.text = '';
                      setState(() {});
                      Navigator.pushReplacementNamed(context, '/ResetPage', arguments: [true, widget.mail]);
                    } else if (userProvider.user.otp.length == 4 && userProvider.user.otp != otp.toString()) {
                      showCustomToast(context, allMessages.value.invalidOtpEntered ?? 'Invalid OTP');
                    } else {
                      showCustomToast(context, allMessages.value.enterAValidOtp ?? 'Enter 4 digits');
                    }
                  },
                  text: allMessages.value.otpButton ?? 'Continue'),
              if (Foundation.kDebugMode) const SizedBox(height: 12),
              if (Foundation.kDebugMode)
                Text(
                  'otp for testing : $otp',
                  style: const TextStyle(fontSize: 12, height: 2),
                ),
              const Spacer(flex: 4)
            ],
          ),
        ),
      ),
    );
  }

  final _pinPutFocusNode = FocusNode();

  Widget otppinfields(otp) {
    final PinTheme pinPutDecoration = PinTheme(
        decoration: BoxDecoration(
      color: Theme.of(context).brightness != Brightness.light
          ? const Color.fromRGBO(50, 50, 50, 1)
          : ColorUtil.whiteGrey,
      borderRadius: BorderRadius.circular(100),
    ));
    final PinTheme focusedDecoration = PinTheme(
        textStyle: const TextStyle(fontFamily: 'Roboto', fontSize: 18, fontWeight: FontWeight.w600),
        decoration: BoxDecoration(
          color: Theme.of(context).primaryColor,
          border: Border.all(width: 1, color: dark(context) ? Colors.white : Theme.of(context).primaryColor),
          borderRadius: BorderRadius.circular(100),
        ));
    final PinTheme followDecoration = PinTheme(
        textStyle: const TextStyle(
            fontFamily: 'Roboto', fontSize: 18, color: Colors.white, fontWeight: FontWeight.w600),
        decoration: BoxDecoration(
          color: Theme.of(context).primaryColor,
          border: Border.all(width: 1, color: dark(context) ? Colors.white : Theme.of(context).primaryColor),
          borderRadius: BorderRadius.circular(100),
        ));
    return Pinput(
      isCursorAnimationEnabled: true,
      length: 4,
      focusNode: _pinPutFocusNode,
      controller: enterOtp,
      submittedPinTheme: followDecoration,
      focusedPinTheme: focusedDecoration,
      onChanged: (value) {
        setState(() {
          userProvider.user.otp = value;
        });
      },
      onCompleted: (value) {
        if (remainingTime == 0) {
          showCustomToast(context, allMessages.value.otpExpired ?? 'OTP is expired');
        } else if (userProvider.user.otp == otp.toString()) {
          showCustomToast(context, allMessages.value.otpVerified ?? 'OTP verified');
          Navigator.pushReplacementNamed(context, '/ResetPage', arguments: [true, widget.mail]);
        } else if (userProvider.user.otp.length == 4 && userProvider.user.otp != otp.toString()) {
          showCustomToast(context, allMessages.value.invalidOtpEntered ?? 'Invalid OTP');
        } else {
          showCustomToast(context, allMessages.value.enterAValidOtp ?? 'Enter 4 digits');
        }
      },
      autofocus: true,
      // listenForMultipleSmsOnAndroid: true,
      autofillHints: const [AutofillHints.oneTimeCode],
      followingPinTheme: pinPutDecoration,
      // androidSmsAutofillMethod: AndroidSmsAutofillMethod.smsUserConsentApi,
      pinAnimationType: PinAnimationType.scale,
      defaultPinTheme: pinPutDecoration,
      errorTextStyle: TextStyle(color: dark(context) ? Colors.red : Colors.black, fontSize: 20.0),
    );
  }
}


// class OtpWrap extends StatefulWidget {
//   const OtpWrap({super.key});

//   @override
//   State<OtpWrap> createState() => _OtpWrapState();
// }

// class _OtpWrapState extends State<OtpWrap> {
//   TextEditingController otp1 = TextEditingController();
//   TextEditingController otp2 = TextEditingController();
//   TextEditingController otp3 = TextEditingController();
//   TextEditingController otp4 = TextEditingController();
//   TextEditingController otp5 = TextEditingController();

//   @override
//   void dispose() {
//     otp1.dispose();
//     otp2.dispose();
//     otp3.dispose();
//     otp4.dispose();
//     otp5.dispose();
//     super.dispose();
//   }

//   @override
//   Widget build(BuildContext context) {
//     var size = MediaQuery.of(context).size;
//     return Column(
//       children: [
//         Padding(
//           padding: const EdgeInsets.symmetric(horizontal: 0),
//           child: Row(
//             mainAxisAlignment: MainAxisAlignment.spaceEvenly,
//             children: [
//               OtpInput(
//                 controller: otp1,
//                 autoFocus: true,
//                  onvalidate: (p0) {
//                   return null;
//                 },
//               ),
//               OtpInput(
//                 controller: otp2,
//                 autoFocus: false,
//                 onvalidate: (p0) {
//                   return null;
//                 },
//                 onsaved: (p0) {
//                   setState(() {});
//                 },
//               ),
//               OtpInput(
//                 controller: otp3,
//                 autoFocus: false,
//                 onvalidate: (p0) {
//                   return null;
//                 },
//               ),
//               OtpInput(
//                 controller: otp4,
//                 onvalidate: (v) {
//                   if (v != null) {
//                     if (v.isEmpty) {
//                       return '';
//                     }
//                   }

//                   return null;
//                 },
//                 onsaved: (value) {
//                   setState(() {});
//                 },
//                 autoFocus: false,
//                    ),
//               OtpInput(
//                 controller: otp5,
//                 onvalidate: (v) {
//                   if (v != null) {
//                     if (v.isEmpty) {
//                       return '';
//                     }
//                   }

//                   return null;
//                 },
//                 onsaved: (value) {
//                   setState(() {});
//                 },
//                 autoFocus: false,
//               ),
//             ],
//           ),
//         ) ]);
//   }}

