[![Build Status](https://travis-ci.org/trivago/rumi.svg?branch=master)](https://travis-ci.org/trivago/rumi)
# rumi
rumi is a container based job execution platform. It reads and executes
 jobs that are defined in a `.rumi.yml` file in a project. The goal is it
 to make job execution more flexible and put the responsibility for
 configuration in projects into the maintainer’s hands.

## Syntax
### Introduction
CI jobs are grouped into stages which are executed sequentially. The jobs
 in each stage are executed in parallel. A failed job aborts the complete
 build.

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
    timeout: 100 // in seconds, default 1200
```
* **docker** (required): docker-compose syntax (https://docs.docker.com/compose/compose-file/) used to start a set of the containers. Can be either `yaml` definition or path to `docker-compose` definition file.
* **commands** (optional, default: image defined command): if defined, the command will be triggered inside the container. If empty - default docker image command will be used.
* **entrypoint** (optional, default: image defined entry point): equivalent to Docker entrypoint option.
* **ci_image** (optional, default: first defined container): in case you don't want to use first defined container as your CI job, you can specify a name for the custom container here.

Job is marked as failed if return status of *ci_image* is other then zero.

### Available environment variables
Rumi injects following environment variables on the test runtime to your test container:
* GIT_COMMIT - contains current commit sha
* GIT_BRANCH - contains current branch name
* GIT_URL - contains url used to checkout the code

### Limitations

1. Port configuration in the `yml` definition is discarded. CI jobs are not able to expose ports. This is likely to change in the later versions. In case you need communication between your containers link Docker containers with each other.

2. ``build`` flag from docker-compose syntax is not supported. You need to pre-build your image, push it to registry and use it with ``image`` flag.

### Sample configurations
// todo

### FAQ

## Local installation/upgrade
1. Use below code to download and install rumi

* linux/mac:
```
wget https://raw.githubusercontent.com/trivago/rumi/master/rumi
mv rumi /usr/local/bin/rumi
chmod +x /usr/local/bin/rumi
```

* windows:

download ``https://raw.githubusercontent.com/trivago/rumi/master/rumi.bat`` and put it in your system PATH

## Local usage:
1. Run the ```rumi``` command in your project directory (where the `.rumi.yml` file is located).

2. You can use ```--job``` and ```--stage``` options to execute only matching stages or jobs. Filtering is case insensitive.

examples:
* ```rumi --job "Unit tests"```
* ```rumi --stage "prepare"```
