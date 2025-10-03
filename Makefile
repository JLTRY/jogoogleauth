VERSION = "1.0.1"
VERSION2 = $(shell echo $(VERSION)|sed 's/ /-/g')
PKG=pkg_jogoogleauth
ZIPFILE = $(PKG)-$(VERSION2).zip
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
	@mv $(INSTALLS:=.zip) $(PKG)/packages/
	@(cd  $(PKG); zip -r ../$@ * $(ZIPIGNORES))
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
	./fixsha.sh $(ZIPFILE) 'update_pkg.xml'

fixcopyrights:
	@find . \( -name '*.php' -o -name '*.ini' -o -name '*.xml' \) -exec ./fixcopyrights.sh {} \;

untabify:
	@find . -name '*.php' -exec $(mkfile_dir)/replacetabs.sh {} \;



