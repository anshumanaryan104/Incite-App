   import 'package:intl/intl.dart';

String timeFormat(DateTime? date,{String pattern = 'dd MMM yyyy'}) {
  final DateFormat formatter =  DateFormat(pattern);
  final String formatted = formatter.format(date!);
  //  final static String endformat = formatter.format(end!);

  return formatted; // something like 2013-04-20
}


 String formatTimeAgo(DateTime dateTime) {
    final Duration difference = DateTime.now().difference(dateTime);

    if (difference.inSeconds < 60) {
      return 'just now';
    } else if (difference.inMinutes < 60) {
      return '${difference.inMinutes} min${difference.inMinutes > 1 ? 's' : ''} ago';
    } else if (difference.inHours < 24) {
      return '${difference.inHours} hour${difference.inHours > 1 ? 's' : ''} ago';
    } else if (difference.inDays < 7) {
      return '${difference.inDays} day${difference.inDays > 1 ? 's' : ''} ago';
    } else {
      return '${(difference.inDays / 7).round()} week${(difference.inDays / 7).round() > 1 ? 's' : ''} ago';
    }
  }
