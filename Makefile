# -------------------------------
# Makefile for generation of a web site with Fantastic Windmill
# Credits to Pelican, from which this file was first obtained and
# then adapted. Thanks!
# -------------------------------

## -----------------
## Shell configuration
## -----------------

# The extglob extension is required to use the negative file pattern in
# the mirror target
SHELL:=/bin/bash -O extglob

## -----------------
## FW configuration
## -----------------

# Where is FW
FW=php fw/fw.php
FWOPTS=--incremental --verbosity 0

# Various dirs and files
BASEDIR=$(PWD)
INPUTDIR=$(BASEDIR)/content
OUTPUTDIR=$(BASEDIR)/public_html

## -----------------
## FTP settings
## -----------------

# FTP Host
FTP_HOST=example.com

# FTP username
FTP_USER=myuser

# Output directory for files
FTP_TARGET_DIR=/httpdocs

# We retrieve the FTP password from a (clear) text file called
# ftp_password.txt. For security reasons, do NOT version this file with Git!
FTP_PASSWORD:=$(shell cat $(BASEDIR)/ftp_password.txt)

## -----------------
## SSH settings
## -----------------
SSH_HOST=localhost
SSH_PORT=22
SSH_USER=root
SSH_TARGET_DIR=/var/www

## -----------------
## Dropbox settings
## -----------------
DROPBOX_DIR=~/Dropbox/Public/

## -----------------
## Make targets
## -----------------

help:
	@echo 'Makefile for a Fantastic Windmill Web site                                        '
	@echo '                                                                       '
	@echo 'Usage:                                                                 '
	@echo '   make html                        (re)generate the web site          '
	@echo '   make publish                     generate using production settings '
	@echo '   make mirror                      mirror all files to output dir     '
	@echo '   make clean                       remove the generated files         '
	@echo '   ssh_upload                       upload the web site via SSH        '
	@echo '   rsync_upload                     upload the web site via rsync+ssh  '
	@echo '   dropbox_upload                   upload the web site via Dropbox    '
	@echo '   ftp_upload                       upload the web site via FTP        '
	@echo '   github                           upload the web site via gh-pages   '
	@echo '                                                                       '

html: template_local
	$(FW) $(FWOPTS)

# New target: mirrors all files of the input directory to the output
# directory, except for source files. This is necessary if we want to have
# files (e.g. images or documents) located in the same directory as the
# documents that refer to them (instead of using the static/ directory)
mirror: 
	rsync -av --exclude='*.md' --exclude="*.yaml" --exclude='*.rst' --exclude='*~' --exclude='*.src' $(INPUTDIR)/ $(OUTPUTDIR)

clean:
	find $(OUTPUTDIR) -mindepth 1 -delete

publish: template_remote $(OUTPUTDIR)/index.html
	$(FW) $(FWOPTS)

ssh_upload: 
	scp -P $(SSH_PORT) -r $(OUTPUTDIR)/* $(SSH_USER)@$(SSH_HOST):$(SSH_TARGET_DIR)

rsync_upload: 
	rsync -e "ssh -p $(SSH_PORT)" -P -rvz --delete $(OUTPUTDIR)/* $(SSH_USER)@$(SSH_HOST):$(SSH_TARGET_DIR)

dropbox_upload: 
	cp -r $(OUTPUTDIR)/* $(DROPBOX_DIR)

ftp_upload: 
	lftp 'ftp://$(FTP_USER):$(FTP_PASSWORD)@$(FTP_HOST)' -e "mirror -R $(OUTPUTDIR) $(FTP_TARGET_DIR) ; quit"

github: 
	ghp-import $(OUTPUTDIR)
	git push origin gh-pages

template_local:
	cp $(BASEDIR)/templates/template_local.php $(BASEDIR)/templates/template_code.php

template_remote:
	cp $(BASEDIR)/templates/template_remote.php $(BASEDIR)/templates/template_code.php

.PHONY: html help clean publish ssh_upload rsync_upload dropbox_upload ftp_upload github mirror template_local template_remote
