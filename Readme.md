# rumi

rumi is a container-based job execution platform.  
It reads and executes job definitions from a `.rumi.yml` file in your project directory.
The goal is to make job execution more flexible and put the responsibility for configuration in the project maintainers hands.

## Syntax

### Introduction

CI jobs are grouped into stages. Stages are executed sequentially. Jobs in each stage are executed in parallel.

First failed job aborts and fails the build itself.

### Stages
```
stages:
  Initialisation:
    Install npm dependencies:
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
The above example illustrates how jobs can be grouped into stages. "Install npm dependencies" and "Install composer dependencies" are executed at the same time. Once all jobs in the given stage are finished, the next stage is started.

You can define an unlimited amount of stages and jobs. You should try to make stage and job names self-explanatory.

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
* **docker** (required): docker-compose syntax (https://docs.docker.com/compose/compose-file/) used to start a set of the containers. Can be either `yaml` definition or path to `docker-compose` definition file.
* **commands** (optional, default: image defined command): if defined, the command will be triggered inside the container. If empty - default docker image command will be used.
* **entrypoint** (optional, default: image defined entry point): equivalent to Docker entrypoint option.
* **ci_image** (optional, default: first defined container): in case you don't want to use first defined container as your CI job, you can specify a name for the custom container here.

Job is marked as failed if return status of *ci_image* is other then zero.

### Limitations

1. Port configuration in the `yml` definition is discarded. CI jobs are not able to expose ports. This is likely to change in the later versions. In case you need communication between your containers link Docker containers with each other.

2. ``build`` flag from docker-compose syntax is not supported. You need to pre-build your image, push it to registry and use it with ``image`` flag.

### Sample configurations
// todo

### FAQ

## How can i execute jobs locally?
It's experimental.

1. **You need to have Docker installed and running on your machine**

2. Download phar from latest stable release attachment:
https://github.com/trivago/rumi/releases

3. Use below code to install phar distribution:
```
mv rumi.phar /usr/local/bin/rumi
chmod +x /usr/local/bin/rumi
```
4. Run the ```rumi``` command in your project directory (where the `.rumi.yml` file is located).
