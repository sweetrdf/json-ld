default:
	docker build . -t jsonld
	docker run -it --rm --name jsonld -v ./../:/home/jsonld/code jsonld bash

clean:
	-@docker ps -a -q | xargs docker stop
	-@docker ps -a -q | xargs docker rm
	-@docker volume ls -f dangling=true -q| xargs docker volume rm
	docker images --quiet --filter=dangling=true | xargs --no-run-if-empty docker rmi -f
