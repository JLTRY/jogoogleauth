VERSION = "1.0.3"
PACKAGE=pkg_jogoogleauth
ZIPFILE = $(PACKAGE)-$(VERSION).zip
UPDATEFILE = update_pkg.xml
ROOT = $(shell pwd)
mkfile_path := $(abspath $(lastword $(MAKEFILE_LIST)))
mkfile_dir := $(dir $(mkfile_path))


# Only set DATE if you need to force the date.  
# (Otherwise it uses the current date.)
# DATE = "February 19, 2011"

all: parts $(ZIPFILE) fixsha

INSTALLS = com_jogoogleauth \
	   plg_jogoogleauth \
	   mod_jogoogleauth

EXTRAS = 

NAMES = $(INSTALLS) $(EXTRAS)

ZIPS = $(NAMES:=.zip)

ZIPIGNORES = -x "*.git*" -x "*.svn*"

parts: $(ZIPS)

%.zip:
	@echo "-------------------------------------------------------"
	@echo "Creating zip file for: $*"
	@rm -f $@
	@(cd $*; zip -r ../$@ * $(ZIPIGNORES))

$(ZIPFILE): $(ZIPS)
	@echo "-------------------------------------------------------"
	@echo "Creating extension zip file: $(ZIPFILE)"
	@mv $(INSTALLS:=.zip) $(PACKAGE)/packages/
	@(cd $(PACKAGE); zip -r ../$@ * $(ZIPIGNORES))
	@echo "-------------------------------------------------------"
	@echo "Finished creating package $(ZIPFILE)."


fixversions:
	@echo "Updating all install xml files to version $(VERSION)"
	@find . \( -name '*.xml' ! -name 'default.xml' ! -name 'metadata.xml' ! -name 'config.xml' \) -exec  ./fixvd.sh {} $(VERSION) \;

revertversions:
	@echo "Reverting all install xml files"
	@find . \( -name '*.xml' ! -name 'default.xml' ! -name 'metadata.xml' ! -name 'config.xml' \) -exec git checkout {} \;

fixsha:
	@echo "Updating update xml files with checksums"
	@(cd $(ROOT);./fixsha.sh $(ZIPFILE) $(UPDATEFILE))

fixcopyrights:
	@find . \( -name '*.php' -o -name '*.ini' -o -name '*.xml' \) -exec ./fixcopyrights.sh {} \;

untabify:
	@find . -name '*.php' -exec $(mkfile_dir)/replacetabs.sh {} \;



