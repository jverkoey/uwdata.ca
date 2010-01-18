
String.prototype.endsWith = function(str) 
{return (this.match(str+"$")==str)}

function is_lighter_word(word) {
  var light_words = [
    'the',
    'of',
    'and',
    'for',
    '-',
    'in',
    'to',
    'part',
    '1',
    '2',
    '3'
  ];
  word = word.toLowerCase();
  for (var ix = 0; ix < light_words.length; ++ix) {
    if (light_words[ix] == word) {
      return true;
    }
  }
  return false;
}

$(function() {
  $("#course_search").val('');
  $("#course_search").placeholder();

  function perform_search(query) {
    if (!query) {
      $('#search').hide();
      return;
    }

    $.ajax({
      dataType: 'jsonp',
      data: {q: query},
      jsonp: 'jsonp_callback',
      url: 'http://dev.api.uwdata.ca/v1/course/search.json',
      success: function(data) {
        if (data.error) {
          $('#search h2').text('No results found, try something like "psychology" or "ENGL 408C"');
          $('#search .results').empty();
          $('#search').show();

        } else {
          var result_or_results = data.total_result_count == 1 ? 'result' : 'results';
          var header = data.total_result_count+' '+result_or_results+' found';
          if (data.page_result_count < data.total_result_count) {
            header += ', showing the first '+data.page_result_count;
          }
          $('#search h2').text(header);
          $('#search .results').empty();

          for (var ix = 0; ix < data.courses.length; ++ix) {
            var course = data.courses[ix].course;
            //console.log(course);
            
            var title_parts = course.title.split(' ');
            var new_title_parts = new Array();
            var all_lighter = false;
            for (var iy = 0; iy < title_parts.length; ++iy) {
              var word = title_parts[iy];

              var had_colon = false;
              if (word.indexOf(':') == word.length - 1) {
                had_colon = true;
                word = word.substr(0, word.length - 1);
              }

              if (all_lighter || is_lighter_word(word)) {
                new_title_parts.push(word);
              } else {
                var emphasize = '<em>'+word+'</em>';
                if (had_colon) {
                  emphasize += ':';
                }
                new_title_parts.push(emphasize);
              }

              if (had_colon) {
                all_lighter = true;
              }
            }
            var title = new_title_parts.join(' ');
            
            $('#search .results')
            .append(
              $('<div>')
              .addClass('course')
              .append(
                $('<div>')
                .addClass('course_number')
                .append(course.faculty_acronym+' '+course.course_number)
              )
              .append(
                $('<div>')
                .addClass('title')
                .append(title)
              )
              .append(
                $('<div>')
                .addClass('description')
                .append(course.description)
              )
            );
          }
          
          $('#search').show();
        }
      }
    });
  }

  var last_search_text = '';
  var timer = null;
  $("#course_search")
  .keyup(function() {
    var search_text = $(this).val();
    if (last_search_text != $(this).val()) {
      clearTimeout(timer);
      timer = setTimeout(function() {perform_search(search_text);}, 500);

      last_search_text = $(this).val();
    }
  });

});
