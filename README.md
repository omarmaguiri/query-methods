# query-methods

Standard CRUD functionality repositories usually have queries on the underlying datastore. 
With QueryMethod, declaring those queries becomes a two-step process:

1. Create a new Instance Of QueryMethod\Repository Class Either With Instance Of Your Model Object Or It's Full Class Name:

  
  $repository = new ..\QueryMethod\Repository(new ..\Model\Person());
  
  
  Or:
  
  
  $repository = new ..\QueryMethod\Repository('..\Model\Person');
  
2. Execute A CRUD Method

  $people = $repository->findByAgeAndCity(20, 'Marrakech');
  
  $people = $repository->findByAgeLessThanAndCity(20, 'Marrakech');
  
  $people = $repository->findByAgeAndCityLike(20, 'Marrakech');
  
  $people = $repository->findByAgeAndCityDateBetween(20, 'Marrakech', '2016-12-01', '2016-12-31');
  
  $people = $repository->findByAgeAndCityOrderByCityAsc(20, 'Marrakech');
  ...
