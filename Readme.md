# rumi

rumi is platform that reads job definition from .rumi.yml file and executes jobs defined there.

Job execution is docker based. It brings flexibility and puts responsibility for configuration in the project's administrator hands.

## Syntax

### Introduction
CI jobs are grouped into stages. Stages are executed sequential. Jobs in each stage are executed in parallel.

First failed job aborts and fails the build itself.

### Stages
```
stages:
  Initialisation:
    Instal npm dependencies:
      ...

    Install composer dependencies:
      ...

  Tests:
    Unit tests:
      ...

    Integration tests:
      ...

    CSS lint:
      ...

```
The above example illustrate how jobs can be grouped into stages. "Install npm dependencies" and "Install composer dependencies" are executed at the same time. Once all jobs in the given stage are finished, next stage is started.

You can define unlimited amount of stages and jobs. Stage and job names can be descriptive.

### Jobs

```
...
  Job name:
    docker:
      // docker-compose syntax
    commands: // optional
      - command one
      - command two
    entrypoint: sh // optional
    ci_image: name_of_the_container // optional
```
* **docker** (required): docker-compose syntax (https://docs.docker.com/compose/compose-file/) used to start set of the containers. Can be either yaml definition or path to docker compose definition file.
* **commands** (optional, default: image defined command): in case if defined, it will be triggered in the container. If empty - default docker image command will be used.
* **entrypoint** (optional, default: image defined entry point): equivalent to docker entrypoint option.
* **ci_image** (optional, default: first defined container): in case you don't want to use first defined container as your CI job, you can specify name of the custom container here.

Job is marked as failed if return status of *ci_image* is other then zero.

### Limitations
Port configuration in the yml definition is discarded. CI jobs are not able to expose ports. This is likely to change in the later versions. In case you need communication between your containers link docker containers with each other.

### Sample configurations
// todo

### FAQ

## How can i execute jobs locally?
Its experimental.

* **You need to have docker installed and running on your machine**

* Use below code to install phar distribution:
```
// todo
```
* In your project directory where .rumi.yml file is located execute it with rumi.phar.
