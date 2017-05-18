savvagoApp
.controller('LessonsCtrl', function($scope, $rootScope, $http, settings){
	$rootScope.current = { backPath: 'home'};
	
	$http.get('api/lessons').then(function(response) {
	      $scope.lessons = response.data;
	  }, function(error) {
	    	toastApiResult(error.data);
	    });
	
    $scope.hoverIn = function(){
        this.hoverEdit = true;
    };

    $scope.hoverOut = function(){
        this.hoverEdit = false;
    };
    
    $scope.deleteLesson = function(lesson) {
    	$http.delete('api/lessons/'+ lesson.lessonId)
		  .then(function(data) {
	    	toastApiResult(data.data);
	    	if (data.data.message.type == 1) {
		    	// remove journey from list
		    	$scope.lessons = $scope.lessons.filter(function(l) {
		    		return l.lessonId !== lesson.lessonId;
		    	});
	    	}
		  }, function(error) {
		    toastApiResult(error.data);
	  });
    };

	$scope.activate = function(event, lesson) {
		lesson.isActive = event.target.checked;
		$http.post('api/lessons/' + lesson.lessonId, lesson)
		  .then(function(data) {
	    	toastApiResult(data.data);
		  }, function(error) {
	    	toastApiResult(error.data);
	  });
	};
    
})
.controller('AddLessonCtrl', function($rootScope, $scope, $http, $routeParams, $location, settings) {
	$scope.addVideo = function() {
		$('#modal-video').modal();		
	};
})
.controller('FormVideoCtrl', function($rootScope, $scope, $http, $routeParams, $location, settings) {
	$rootScope.current = { backPath: settings.appPath+'#/'};

	$scope.loadJourneys = function() {
		$http.get('api/journeys/matching/' + $scope.lesson.tags)
		.then(function( data ) {
		    $scope.journeys = data.data;
		  }, function(error) {
		    	toastApiResult(error.data);
		});
	};
	
	var lessonId = $routeParams.lessonId;
	if (lessonId > 0) {
		$http.get('api/lessons/' + lessonId).then(function(response) {
	      $scope.lesson = response.data;
	      $scope.loadJourneys();
	  }, function(error) {
	    	toastApiResult(error.data);
	    });		
	} else {
		$scope.lesson = {};
	}
	$scope.loadContentPreview = function() {
		var link = $scope.lesson.link;
		
		var ytUrl = 'https://www.youtube.com/oembed?url=' + link + '&format=json';

		$http.get(ytUrl)
		.then(function( data ) {
		    console.log(data);
		  }, function(error) {
		    	console.log(error);
		});

	};
	
	
	$scope.submitForm = function(isValid) {
	    // check to make sure the form is completely valid
		if ($scope.lesson.lessonId > 0) {
			// update
	    	$http.post('api/lessons/'+$scope.lesson.lessonId, $scope.lesson)
	  		  .then(function(data) {
	  		    $scope.errors = data.data.message.propertyMessages;
		    	toastApiResult(data.data);
		    	$('#modal-video').modal('hide');
			  }, function(error) {
			    	toastApiResult(error.data);
	  		  });
		    	
		} else {
			//create
	    	$http.post('api/lessons', $scope.lesson)
	  		  .then(function(data) {
	  		    $scope.errors = data.data.message.propertyMessages;
  		    	toastApiResult(data.data);
		    	$('#modal-video').modal('hide');
			  }, function(error) {
			    	toastApiResult(error.data);
			  });
		}
	  };
	  
});


savvagoApp.config(['$routeProvider', 'settingsProvider', function($routeProvider, settingsProvider) {
	
  $routeProvider
  .when("/create", {
	templateUrl : settingsProvider.$get().templatePath + 'form.html'
	  })
	  .when("/edit/:lessonId", {
	templateUrl : settingsProvider.$get().templatePath + 'form.html'
	  })
	  .when("/", {
	templateUrl : settingsProvider.$get().templatePath + 'lessons.html'
	  })
	  .otherwise({
	templateUrl : settingsProvider.$get().templatePath + 'lessons.html'
		  })
	}]);