savvagoApp
.controller('JourneysCtrl', function($scope, $rootScope, $http, settings){
	$rootScope.current = { backPath: 'home'};
	
	
	$http.get('api/journeys').then(function(response) {
		console.log('load journeys');
	      $scope.journeys = response.data;
	    });
	
    $scope.hoverIn = function(){
        this.hoverEdit = true;
    };

    $scope.hoverOut = function(){
        this.hoverEdit = false;
    };
    
    $scope.deleteJourney = function(journey) {
    	$http.delete('api/journeys/'+journey.journeyId)
		  .then(function(data) {
	    	toastApiResult(data.data);
	    	if (data.data.message.type == 1) {
		    	// remove journey from list
		    	$scope.journeys = $scope.journeys.filter(function(j) {
		    		return j.journeyId !== journey.journeyId;
		    	});
	    	}
	  });
    };

	$scope.activate = function(event, journey) {
		journey.isActive = event.target.checked;
		$http.post('api/journeys/'+journey.journeyId, journey)
		  .then(function(data) {
	    	toastApiResult(data.data);
	  });
	};
    
    
})
.controller('FormCtrl', function($rootScope, $scope, $http, $routeParams, $location, settings) {
	$rootScope.current = { backPath: settings.appPath+'#/'};

	$scope.loadLessons = function() {
		$http.get('api/lessons/matching/' + $scope.journey.tags)
		.then(function( data ) {
		    $scope.lessons = data.data;
		});
	};
	
	var journeyId = $routeParams.journeyId;
	if (journeyId > 0) {
		$http.get('api/journeys/' + journeyId).then(function(response) {
			console.log('load journey');
	      $scope.journey = response.data;
	      $scope.loadLessons();
	    });		
	} else {
		$scope.journey = {};
	}
	
	$scope.submitForm = function(isValid) {
	    // check to make sure the form is completely valid
		if ($scope.journey.journeyId > 0) {
			// update
	    	$http.post('api/journeys/'+$scope.journey.journeyId, $scope.journey)
	  		  .then(function(data) {
	  		    $scope.errors = data.data.message.propertyMessages;
		    	toastApiResult(data.data);
		    	if (data.data.message.type == 1) {
		    		$location.path($scope.current.backPath);
		    	}
	  		  });
		    	
		} else {
			//create
	    	$http.post('api/journeys', $scope.journey)
	  		  .then(function(data) {
	  		    $scope.errors = data.data.message.propertyMessages;
  		    	toastApiResult(data.data);
		    	if (data.data.message.type == 1) {
		    		$location.path($scope.current.backPath);
		    	}
  		  });
		}
	  };
	  
});


savvagoApp.config(['$routeProvider', 'settingsProvider', function($routeProvider, settingsProvider) {
	
  $routeProvider
  .when("/create", {
	templateUrl : settingsProvider.$get().templatePath + 'form.html'
	  })
	  .when("/edit/:journeyId", {
	templateUrl : settingsProvider.$get().templatePath + 'form.html'
	  })
	  .when("/", {
	templateUrl : settingsProvider.$get().templatePath + 'journeys.html'
	  })
	  .otherwise({
	templateUrl : settingsProvider.$get().templatePath + 'journeys.html'
		  })
	}]);