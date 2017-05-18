savvagoApp
.controller('UsersCtrl', function($scope, $rootScope, $http, settings){
	$rootScope.current = { backPath: 'home'};
	
	
	$http.get('api/users').then(function(response) {
		console.log('load users');
	      $scope.users = response.data;
	    });
	
    $scope.hoverIn = function(){
        this.hoverEdit = true;
    };

    $scope.hoverOut = function(){
        this.hoverEdit = false;
    };
    
    $scope.deleteUser = function(user) {
    	$http.delete('api/users/'+user.userId)
		  .then(function(data) {
	    	toastApiResult(data.data);
	    	if (data.data.message.type == 1) {
		    	// remove user from list
		    	$scope.users = $scope.users.filter(function(j) {
		    		return j.userId !== user.userId;
		    	});
	    	}
	  });
    };

	$scope.activate = function(event, user) {
		user.isActive = event.target.checked;
		$http.post('api/users/'+user.userId+'/activate', user)
		  .then(function(data) {
	    	toastApiResult(data.data);
	  });
	};
    
    
})
.controller('FormCtrl', function($rootScope, $scope, $http, $routeParams, $location, settings) {
	$rootScope.current = { backPath: settings.appPath+'#/'};
	
	var userId = $routeParams.userId;
	if (userId > 0) {
		$http.get('api/users/' + userId).then(function(response) {
			console.log('load user');
	      $scope.user = response.data;
	    });		
	} else {
		$scope.user = {};
	}
	
	$scope.submitForm = function(isValid) {
	    // check to make sure the form is completely valid
		if ($scope.user.userId > 0) {
			// update
	    	$http.post('api/users/'+$scope.user.userId, $scope.user)
	  		  .then(function(data) {
	  		    $scope.errors = data.data.message.propertyMessages;
		    	toastApiResult(data.data);
		    	if (data.data.message.type == 1) {
		    		$location.path($scope.current.backPath);
		    	}
	  		  });
		    	
		} else {
			//create
	    	$http.post('api/users', $scope.user)
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
	  .when("/edit/:userId", {
	templateUrl : settingsProvider.$get().templatePath + 'form.html'
	  })
	  .when("/", {
	templateUrl : settingsProvider.$get().templatePath + 'users.html'
	  })
	  .otherwise({
	templateUrl : settingsProvider.$get().templatePath + 'users.html'
		  })
	}]);