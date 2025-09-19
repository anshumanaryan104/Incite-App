import '../model/blog.dart';

class BlogListHolder {
  DataModel _list = DataModel();
  BlogType blogType = BlogType.feed;
  int _index = 0;

  int getIndex() => _index;
  BlogType getBlogType() => blogType;
  DataModel getList() => _list;

  void setIndex(int index) {
    _index = index;
  }

  void setBlogType(BlogType list) {
    blogType = list;
  }

  void setList(DataModel list) {
    _list = list;
  }

  Future updateList(DataModel lists) async {
    _list.currentPage = lists.currentPage;
    _list.firstPageUrl = lists.firstPageUrl;
    _list.lastPageUrl = lists.lastPageUrl;
    _list.nextPageUrl = lists.nextPageUrl;
    _list.to = lists.to;
    _list.prevPageUrl = lists.prevPageUrl;
    _list.lastPage = lists.lastPage;
    _list.from = lists.from;    
    _list.blogs.addAll(lists.blogs.toSet().toList());
  }


  void clearList() {    
    _list = DataModel();
  }
}

BlogListHolder blogListHolder = BlogListHolder();
BlogListHolder blogListHolder2 = BlogListHolder();

enum BlogType{
  feed,
  allnews,
  featured,
  category,
  bookmarks,
  search
}